<?php
namespace SaQle\Migration\Managers;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;
use SaQle\Migration\Models\Migration;
use SaQle\Commons\FileUtils;
use SaQle\Migration\Tracker\MigrationTracker;
use SaQle\Orm\Entities\Field\Types\{TextType, NumberType, FileField, OneToOne, ManyToMany, OneToMany, Pk};
use SaQle\Orm\Entities\Field\Types\Base\Relation;
use SaQle\Orm\Entities\Model\Interfaces\IThroughModel;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Orm\Connection\Connection;

class ContextManager implements IMigrationManager{
     use FileUtils;

     private function get_class_namespace(string $long_class_name){
         $nameparts = explode("\\", $long_class_name);
         array_pop($nameparts);
         return implode("\\", $nameparts);
     }

     private function execute($pdo, $sql, $data = null){
         $statement = $pdo->prepare($sql);
         $response  = $statement->execute($data);
         return ['statement' => $statement, 'response' => $response];
     }

     private function get_class_name(string $long_class_name, bool $include_namespace = false){
         if($include_namespace)
             return $long_class_name;

         $nameparts = explode("\\", $long_class_name);
         return end($nameparts);
     }
     
     private function is_model_defined($model_class, $project_root){
         $mnparts = explode("\\", $model_class);
         $root = array_shift($mnparts);
         $root = strtolower($root);

         $model_file_path = strtolower(implode(DIRECTORY_SEPARATOR, $mnparts)).".php";
         if($root == "saqle"){
             $project_root_parts = explode(DIRECTORY_SEPARATOR, $project_root);
             array_pop($project_root_parts);
             $saqle_root = strtolower(implode(DIRECTORY_SEPARATOR, $project_root_parts))."/saqle";
             $model_file_path = $saqle_root."/".$model_file_path;
         }else{
             $model_file_path = $project_root."/".$model_file_path;
         }

         return file_exists($model_file_path);
     }

     private function get_context_classes($db_context){
         //There must exist at least one db context class
         if(!DB_CONTEXT_CLASSES){
             throw new \Exception("The project has not declared any database context classes!");
         }

         /**
          * If a db context is provided, 
          * 1. Confirm that it exists in the db context classes list,
          * 2. Confirm that the class actually exists.
          * */
         $context_classes = [];
         $defined_classes = array_keys(DB_CONTEXT_CLASSES);
         if($db_context){
             for($x = 0; $x < count($defined_classes); $x++){
                 if(str_contains($defined_classes[$x], $db_context) && class_exists($defined_classes[$x])){
                     $context_classes[] = $defined_classes[$x];
                     break;
                 }
             }
         }else{
             for($x = 0; $x < count($defined_classes); $x++){
                 if( class_exists($defined_classes[$x]) ){
                     $context_classes[] = $defined_classes[$x];
                 }
             }
         }

         if(!$context_classes){
                throw new \Exception("Ensure there is at least one defined database context!");
         }

         return $context_classes;
     }

     private function get_snapshot($migration_name, $timestamp, $dirname, $ctxname, $project_root){
         $class_name  = "{$ctxname}_{$timestamp}_{$migration_name}";
         $snap_folder = $dirname."/snapshots";
         $file_name   = $snap_folder."/".$class_name.".php";
         if(!file_exists($file_name)){
            throw new \Exception("The snapshot file({$class_name}) cannot be located!");
         }

         require_once $file_name;

         $instance      = new $class_name();
         $raw_models    = $instance->get_models();
         $raw_fields    = $instance->get_model_fields();
         $unique_fields = $instance->get_unique_fields();

         $clean_models   = [];
         $clean_fields   = [];

         foreach($raw_models as $n => $m){
             if($this->is_model_defined($m, $project_root)){
                 $clean_models[$n] = $m;

                 if(isset($raw_fields[$n]) && is_array($raw_fields[$n])){
                     $clean_fields[$n] = array_filter($raw_fields[$n], function($value, $key){
                         return $value['def'] !== '';
                     }, ARRAY_FILTER_USE_BOTH);
                 }
             }
         }

         return [$clean_models, $clean_fields, $unique_fields];
     }

     private function get_path_from_namespace(string $namespace, $project_root){
         $mnparts = explode("\\", $namespace);
         $root = array_shift($mnparts);
         $root = strtolower($root);
         $path = strtolower(implode(DIRECTORY_SEPARATOR, $mnparts))."/";
         if($root == "saqle"){
             $project_root_parts = explode(DIRECTORY_SEPARATOR, $project_root);
             array_pop($project_root_parts);
             $saqle_root = strtolower(implode(DIRECTORY_SEPARATOR, $project_root_parts))."/saqle";
             $path = $saqle_root."/".$path;
         }else{
             $path = $project_root."/".$path;
         }
         return $path;
     }

     private function write_through_model($primary_model_instance, $foreign_model_instance, $project_root){
         $pnamespace = $this->get_class_namespace($primary_model_instance::class);
         $througnamespace = $pnamespace."\\Throughs";
         $fnamespace = $this->get_class_namespace($foreign_model_instance::class);
         $classname = $this->get_class_name($primary_model_instance::class).$this->get_class_name($foreign_model_instance::class);

         $pmodel_name = strtolower($this->get_class_name($primary_model_instance::class));
         $fmodel_name = strtolower($this->get_class_name($foreign_model_instance::class));
         $o_pmodel_name = $this->get_class_name($primary_model_instance::class);
         $o_fmodel_name = $this->get_class_name($foreign_model_instance::class);
         $pmodel_pk = $primary_model_instance->meta->pk_name;
         $fmodel_pk = $foreign_model_instance->meta->pk_name;

         $template = "<?php\n";
         $template .= "/**\n";
         $template .= "* This is an auto generated file.\n"; 
         $template .= "*\n";
         $template .= "* The code here is designed to work as is, and must not be modified unless you know what you are doing.\n";
         $template .= "*\n";
         $template .= "* If you find ways that the code can be improved to enhance speed, efficiency or memory, be kind enough\n";
         $template .= "* to share with the author at wycliffomondiotieno@gmail.com or +254741142038. The author will not mind a cup\n";
         $template .= "* of coffee either.\n";
         $template .= "*\n";
         $template .= "* Commands to generate file:\n";
         $template .= "* 1. php manage.php make:migrations\n";
         $template .= "* 2. php manage.php make:throughs\n";
         $template .= "* On your terminal, cd into project root and run the above commands\n";
         $template .= "* \n";
         $template .= "* This is a through model for ".$o_pmodel_name." and ".$o_fmodel_name.". A through model is generated behind the scenes\n";
         $template .= "* when a model defines a many to many field with another model.\n";
         $template .= "* */\n\n";
         $template .= "namespace ".$througnamespace.";\n\n";
         $template .= "use SaQle\\Orm\Entities\\Model\\Interfaces\\IThroughModel;\n";
         $template .= "use SaQle\\Orm\Entities\Field\\Types\\{Pk, OneToOne};\n";
         $template .= "use SaQle\\Orm\Entities\\Model\\Schema\\{Model, TableInfo};\n";
         $template .= "use ".$pnamespace."\\".$o_pmodel_name.";\n";
         $template .= "use ".$fnamespace."\\".$o_fmodel_name.";\n";
         /*$template .= "use SaQle\\Core\\Assert\\Assert;\n\n";*/
         $template .= "class {$classname} extends Model implements IThroughModel{\n\n";
         /*$template .= "\tprivate static array $"."include_fields = [\n";
         $template .= "\t\t".$o_pmodel_name."::class => '".$fmodel_name."',\n";
         $template .= "\t\t".$o_fmodel_name."::class => '".$pmodel_name."'\n";
         $template .= "\t];\n\n";*/
         /**
          * Define the constructor
          * */
         $template .= "\tprotected function model_setup(TableInfo $"."meta) : void{\n";
         $template .= "\t\t$"."meta->fields = [\n";
         $template .= "\t\t\t'id' => new Pk(),\n";
         $template .= "\t\t\t'".$pmodel_name."' => new OneToOne(fmodel: ".$o_pmodel_name."::class, pk: '".$pmodel_name."_id', fk: '".$pmodel_pk."', column_name: '".$pmodel_name."_id'),\n";
         $template .= "\t\t\t'".$fmodel_name."' => new OneToOne(fmodel: ".$o_fmodel_name."::class, pk: '".$fmodel_name."_id', fk: '".$fmodel_pk."', column_name: '".$fmodel_name."_id')\n";
         $template .= "\t\t];\n\n";
         $template .= "\t\t$"."meta->unique_fields   = ['".$pmodel_name."', '".$fmodel_name."'];\n";
         $template .= "\t\t$"."meta->unique_together = true;\n";
         $template .= "\t}\n\n";

         $template .= "}\n";

         $path = $this->get_path_from_namespace($througnamespace, $project_root);
         if(!file_exists($path)){
            mkdir($path, 0755);
         }
         $filename = $path."/".strtolower($classname).".php";

         file_put_contents($filename, $template);
         return $througnamespace."\\".$classname;
     }

     private function has_manytomany_relationship_with(Model $model1, Model $model2){
         $relation = false;
         for($f = 0; $f < count($model1->meta->nav_field_names); $f++){
             $field = $model1->meta->fields[$model1->meta->nav_field_names[$f]];
             if($field->get_relation()->fmodel == $model2::class && $field instanceof ManyToMany){
                $relation = $field;
             }
         }
         return $relation;
     }

     private function extract_model_fields($models, $project_root){
         $model_fields = [];
         foreach($models as $n => $m){
             if(!$this->is_model_defined($m, $project_root))
                 continue;

             $model_fields[$n] = []; //all the fields defined on the model.
             $mfields = $m::state()->meta->fields;
             foreach($mfields as $mfn => $mfv){
                 $mfvdef = $mfv->get_field_definition();
                 if($mfvdef){
                     $db_col_name = $mfv->column_name;
                     $model_fields[$n][$db_col_name] = ['field' => $mfv::class, 'params' => $mfv->get_kwargs(), 'def' => $mfvdef];
                 }
             }
         }
         return $model_fields;
     }

     private function extract_through_models($models, $project_root, &$manytomany_throughs){
         $through_models = [];
         foreach($models as $n => $m){
             if(!$this->is_model_defined($m, $project_root))
                 continue;

             $mi = $m::state();
             $mfields = $mi->meta->fields;
             foreach($mfields as $mfn => $mfv){
                 if($mfv instanceof ManyToMany){
                     echo "Generating throughs for {$mfn} in {$n}!\n";
                     $relation = $mfv->get_relation();
                     if($relation->through)
                         continue;
                     
                     print_r($relation);
                     //get the foreign model.
                     $fmodel = $relation->fmodel;
                      //1. Foreign key model must have a ManyToMany field pointing to current table also defined on it.
                      //2. Foreign key model must also be defined for the through table to be generated
                      //3. The name of the through table will be generated by combining the two class names names.
                     if(!$this->is_model_defined($fmodel, $project_root))
                         continue;

                     echo "The foreign model {$fmodel} is defined\n";
                     $fmodel_instance = $fmodel::state();

                     if($this->has_manytomany_relationship_with($fmodel_instance, $mi) === false)
                         continue;

                     echo "The foreign model {$fmodel} has a relationship with primary model: {$m}\n";
                     $first_pointer = strtolower($this->get_class_name($mi::class).$this->get_class_name($fmodel));
                     $other_pointer = strtolower($this->get_class_name($fmodel).$this->get_class_name($mi::class));

                     if(in_array($first_pointer, $manytomany_throughs) || in_array($other_pointer, $manytomany_throughs))
                         continue;

                     echo "A through field has not been generated for current run!\n";
                     /*$tm = $this->write_through_model($mi, $fmodel_instance, $project_root);
                     $through_models[$first_pointer] = $tm;*/

                     array_push($manytomany_throughs, $first_pointer);
                     array_push($manytomany_throughs, $other_pointer);
                 }
             }
         }
         return $through_models;
     }

     private function write_database_snapshot($migration_name, $timestamp, $models, $unique_fields, $dirname, $ctxname, $project_root){
         $class_name  = "{$ctxname}_{$timestamp}_{$migration_name}";
         $snap_folder = $dirname."/snapshots";
         $file_name   = $snap_folder."/".$class_name.".php";

         $models_template = "";
         $fields_template = "";
         foreach($models as $n => $m){
             if($this->is_model_defined($m, $project_root)){
                 $models_template .= "\t\t\t'".$n."' => '".$m."',\n";
                 $mfields = $m::state()->meta->fields;
                 $fields_template.= "\t\t\t'".$n."' => [\n";
                 foreach($mfields as $mfn => $mfv){
                     $db_col_name = $mfv->column_name;
                     $fields_template .= "\t\t\t\t'".$db_col_name."' => [\n";
                     $fields_template .= "\t\t\t\t\t'field' => '".$mfv::class."',\n";
                     $fields_template .= "\t\t\t\t\t'def' => '".$mfv->get_field_definition()."',\n";
                     $fields_template .= "\t\t\t\t\t'params' => [\n"; 

                     $params = $mfv->get_kwargs();
                     foreach($params as $pk => $pv){
                         if(is_array($pv)){
                            $pvv = array_map(function($_pv){
                                return "'".$_pv."'";
                            }, $pv);
                            $pvv = "[".implode(", ", $pvv)."]";
                         }else{
                            $pvv = "'".(string)$pv."'";
                         }
                         $fields_template .= "\t\t\t\t\t\t'".(string)$pk."' => ".(string)$pvv.",\n";
                     }
                     $fields_template .= "\t\t\t\t\t],\n";
                     $fields_template .= "\t\t\t\t],\n";
                 }
                 $fields_template.= "\t\t\t],\n";
             }
         }

         $uniques_template = "";
         foreach($unique_fields as $n => $u){
             $ut = $u['unique_together'] ? 'true' : 'false';

             $uniques_template .= "\t\t\t'".$n."' => [\n";
             $uniques_template .= "\t\t\t\t'unique_together' => ".$ut.",\n";
             $uniques_template .= "\t\t\t\t'fields' => [\n";
             foreach($u['fields'] as $uf){
                 $uniques_template .= "\t\t\t\t\t'".$uf."',\n"; 
             }
             $uniques_template .= "\t\t\t\t]\n";
             $uniques_template .= "\t\t\t],\n";
         }

         $template = "<?php\n";
         $template .= "/**\n";
         $template .= "* This is an auto generated file.\n"; 
         $template .= "*\n";
         $template .= "* The code here is designed to work as is, and must not be modified unless you know what you are doing.\n";
         $template .= "*\n";
         $template .= "* If you find ways that the code can be improved to enhance speed, efficiency or memory, be kind enough\n";
         $template .= "* to share with the author at wycliffomondiotieno@gmail.com or +254741142038. The author will not mind a cup\n";
         $template .= "* of coffee either.\n";
         $template .= "*\n";
         $template .= "* Commands to generate file:\n";
         $template .= "* 1. php manage.php make:migrations\n";
         $template .= "* On your terminal, cd into project root and run the above commands\n";
         $template .= "* \n";
         $template .= "* A database snapshot keeps a record of the database, tables and columns structures as at the time makemigrations is run.\n";
         $template .= "* */\n\n";
         $template .= "use SaQle\\Migration\\Base\\DbSnapshot;\n\n";
         $template .= "class {$class_name} extends DbSnapshot{\n";
         
         //get the models.
         $template .= "\tpublic function get_models(){\n";
         $template .= "\t\treturn [\n";
         $template .= $models_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         
         //Get the model fields.
         $template .= "\tpublic function get_model_fields(){\n";
         $template .= "\t\treturn [\n";
         $template .= $fields_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";

         //get unique fields.
         $template .= "\tpublic function get_unique_fields(){\n";
         $template .= "\t\treturn [\n";
         $template .= $uniques_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";

         $template .= "}\n";

         //create snapshot folder
         if(!file_exists($snap_folder)){
             mkdir($snap_folder);
         }

         file_put_contents($file_name, $template);
     }

     private function extract_unique_fields($models, $project_root){
         $unique_fields = [];
         foreach($models as $n => $m){
             if(!$this->is_model_defined($m, $project_root))
                 continue;

             $mi = $m::state();
             $unique_fields[$n] = ['unique_together' => $mi->meta->unique_together, 'fields' => $mi->meta->unique_fields];
         }

         return $unique_fields;
     }

     public function get_context_snapshot(...$options){
         $db_context     = $options['db_context'] ?? null;
         $project_root   = $options['project_root'] ?? null;
         $app_name       = $options['app_name'] ?? null;
         $migration_name = $options['migration_name'] ?? null;
         $timestamp      = $options['timestamp'] ?? null;
         $tracker        = $options['tracker'];

         $context_classes = $this->get_context_classes($db_context);
         $context_snapshot = [];

         foreach($context_classes as $ctx){
             $context_snapshot[$ctx] = [];
             $ctxparts = explode("\\", $ctx);
             $ctxname  = end($ctxparts);

             //Acquire models registered with db context
             $models   = new $ctx()->get_permanent_models();

             //Acquire model fields for models registered with db context.
             $model_fields = $this->extract_model_fields($models, $project_root);

             //acquire unique fields
             $unique_fields = $this->extract_unique_fields($models, $project_root);
             $last_unique_fields = [];
             
             $a        = new \ReflectionClass($ctx);
             $filename = $a->getFileName();
             $dirname  = pathinfo($filename)['dirname'];

             $connection_params = DB_CONTEXT_CLASSES[$ctx];
             $connection_params['name'] = ''; //we are connecting without a database, therefore set the database name to empty string
             $connection = resolve(Connection::class, $connection_params);

             $this->write_database_snapshot($migration_name, $timestamp, $models, $unique_fields, $dirname, $ctxname, $project_root);

             $added_models    = $models;
             $removed_models  = [];
             $maintained_models = [];

             $added_columns   = [];
             $removed_columns = [];

             try{
                 $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
                 $data = [DB_CONTEXT_CLASSES[$ctx]['name']];
                 $statement = $this->execute($connection, $sql, $data)['statement'];
                 $object = $statement->fetchObject(); 

                 if($object){
                     //Database exists, acquire the timestamp for the last snapshot.
                     $last_migration = Migration::get()
                     ->order(fields: ['migration_timestamp'], direction: 'DESC')
                     ->limit(page: 1, records: 1)
                     ->first_or_default();
                     if($last_migration){

                         [$last_models, $last_model_fields, $last_unique_fields] = $this->get_snapshot(
                            $last_migration->migration_name, 
                            $last_migration->migration_timestamp, 
                            $dirname, 
                            $ctxname,
                            $project_root
                         );

                         //Which new models have been added.
                         $added_models = array_diff($models, $last_models);

                         //Which models have been removed
                         $removed_models = array_diff($last_models, $models);
                
                         //Which models have been maintained.
                         $maintained_models = array_intersect($models, $last_models);
                         
                         $all_model_fields = $model_fields;
                         $all_last_model_fields = $last_model_fields;

                         foreach($maintained_models as $table_name => $model_name){
                             $current_column_keys  = array_keys($all_model_fields[$table_name]);
                             $previous_column_keys = array_keys($all_last_model_fields[$table_name]);

                             $added_column_keys = array_diff($current_column_keys, $previous_column_keys);
                             $removed_column_keys = array_diff($previous_column_keys, $current_column_keys);

                             if($added_column_keys){
                                 $added_settings = ['name' => $table_name, 'model' => $model_name, 'columns' => []];
                                 foreach($added_column_keys as $ack){
                                     $added_settings['columns'][$ack] = $all_model_fields[$table_name][$ack]['def'];
                                 }
                                 $added_columns[] = $added_settings;
                             }
                             if($removed_column_keys){
                                 $removed_settings = ['name' => $table_name, 'model' => $model_name, 'columns' => []];
                                 foreach($removed_column_keys as $rck){
                                     $removed_settings['columns'][$rck] = $all_last_model_fields[$table_name][$rck]['def'];
                                 }
                                 $removed_columns[] = $removed_settings;
                             }
                         }
                     }
                 }
             }catch(\Exception $ignore){
                 
             }

             $context_snapshot[$ctx]['tables'] = [$added_models, $removed_models, $maintained_models];
             $context_snapshot[$ctx]['columns'] = [$added_columns, $removed_columns];
             $context_snapshot[$ctx]['unique'] = [$unique_fields, $last_unique_fields];
         }

         return $context_snapshot;
     }

     public function make_collections(string $project_root, $app_name = null, $db_context = null){
         $context_classes = $this->get_context_classes($db_context);
         foreach($context_classes as $ctx){
             $models   = new $ctx()->get_permanent_models(); 
             foreach($models as $table_name => $table_schema){
                 
             }
         }
     }

     public function make_models(string $project_root, $app_name = null, $db_context = null){
         $context_classes = $this->get_context_classes($db_context);
         foreach($context_classes as $ctx){
             $models   = new $ctx()->get_permanent_models(); 
             foreach($models as $table_name => $table_schema){
                 
             }
         }
     }

     public function seed_database($project_root){
          if(DB_SEEDER !== ''){
             $path = $this->get_path_from_namespace(DB_SEEDER, $project_root);
             $pathparts = explode(DIRECTORY_SEPARATOR, $path);
             array_pop($pathparts);
             $path = implode(DIRECTORY_SEPARATOR, $pathparts);
             $seeder = DB_SEEDER;
             $seeds = $seeder::get_seeds();
             foreach($seeds as $c => $seed){
                 $model = $seed['model'];
                 $file  = $path.DIRECTORY_SEPARATOR.$seed['file'];

                 echo "Now seeding for model: {$model}\n";
                 $data = require_once $file;
                 $seeded_data = $model::new($data)->save();
                 echo "Model: {$model} seeded!\n\n";
             }
          }
     }

     public function reset_database($project_root){
         foreach(DB_CONTEXT_CLASSES as $classname => $params){
             $classinstance = new $classname();
             $models = $classinstance->get_models();
             foreach($models as $table_name => $modelclass){
                 if($table_name !== 'model_temp_ids'){
                     $modelclass::empty()->now();
                 }
             }
         }
         $this->seed_database($project_root);
     }

     public function make_throughs(string $project_root, $app_name = null, $db_context = null){
         $context_classes = $this->get_context_classes($db_context);
         $manytomany_throughs = [];
         
         foreach($context_classes as $ctx){
             $models = new $ctx()->get_permanent_models();
             $this->extract_through_models($models, $project_root, $manytomany_throughs);
         }
     }

     public function make_superuser(string $project_root, $email, $password){
         $model_class_schema = AUTH_MODEL_CLASS;
         $model_class        = $model_class_schema;
         $user               = (new $model_class(...[
            'username'       => $email,
            'password'       => md5($password),
            'first_name'     => 'Super',
            'last_name'      => 'User',
            'label'          => 'SUPER',
            'gender'         => 'male',
            'dob'            => '1993-08-15',
            'is_online'      => 0,
            'account_status' => 3,
            'disabled'       => 0
         ]))->save();
         if($user){
             echo "Super user created!\n";
         }
     }

     public function start_apps(string $project_root, $name){
         echo "Starting apps! {$name}\n";
     }

     private function create_folder($root, $folders){
         foreach($folders as $key => $value){
             $path = $root."/".$key;
             if(is_array($value)){ //this is a folder
                 if(@mkdir($path)){
                     $this->create_folder($path, $value);
                 }
             }else{ //this is a file
                 @file_put_contents($path, $value);
             }
         }
     }

     public function start_project($name, $index, $session, $web, $api, $dbcontext, $signin, $signout, $usermodel, $userschema, 
        $usercollection, $welcomeemailsetup, $accapiroutes, $accwebroutes, $accountservice, $authservice, $signinhtml, $homehtml,
        $homecontroller, $config, $dbseed, $dirmanager, $showfile, $isbackoffice, $welcomeemail, $htaccess, $manager){
         $current_dir = __DIR__;
         $dirname = dirname(dirname(dirname($current_dir)));
         $projectfolders[strtolower($name)] = [
            'apps' => [
                'account' => [
                    'controllers'   => [
                        'signin.php'  => $signin,
                        'signout.php' => $signout
                    ],
                    'data'          => [
                        'snapshots' => [],
                        'accountsdbcontext.php' => $dbcontext
                    ],
                    'models'        => [
                        'collections' => [
                            'usercollection.php' => $usercollection
                        ],
                        'schema'      => [
                            'userschema.php' => $userschema
                        ],
                        'user.php'    => $usermodel
                    ],
                    'notifications' => [
                        'welcomeemailsetup.php' => $welcomeemailsetup
                    ],
                    'observers'     => [],
                    'routes'        => [
                        'api.php' => $accapiroutes,
                        'web.php' => $accwebroutes
                    ],
                    'services'      => [
                        strtolower($name)."accountservice.php" => $accountservice,
                        strtolower($name)."authservice.php"    => $authservice
                    ],
                    'templates'     => [
                        'signin.html' => $signinhtml,
                    ]
                ]
            ], 
            'config' => [
                'seeds' => [
                    strtolower($name)."dbseed.php" => $dbseed
                ],
                'config.php' => $config
            ], 
            'controllers' => [
                'home.php'  => $homecontroller
            ], 
            'dirmanager'  => [
                strtolower($name)."dirmanager.php" => $dirmanager,
                'showfile.php'                        => $showfile
            ], 
            'migrations'  => [], 
            'media'       => [], 
            'models'      => [
                'collections' => [],
                'schema'      => []
            ], 
            'observers'   => [],
            'permissions' => [
                'isbackoffice' => $isbackoffice
            ],
            'routes'      => [
                'web' => $web,
                'api' => $api
            ],
            'services' => [],
            'session' => [
                strtolower($name)."sessionhandler.php" => $session
             ],
            'static' => [
                'css'    => [],
                'js'     => [],
                'images' => [
                    'layout' => [],
                    'icons'  => []
                ],
                'font'   => []
            ],
            'templates' => [
                 'home.html'  => $homehtml,
                 'welcomeemail.html' => $welcomeemail
            ],
            '.htaccess' => $htaccess,
            'manage.php' => $manager,
            'index.php' => $index
         ];
         $this->create_folder($dirname, $projectfolders);
         echo "Starting project! {$name}\n";
     }
}
