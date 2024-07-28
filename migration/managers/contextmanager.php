<?php
namespace SaQle\Migration\Managers;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;
use SaQle\Services\Container\Cf;
use SaQle\Services\Container\ContainerService;
use SaQle\Migration\Models\Migration;
use SaQle\Dao\Field\Types\ManyToMany;

class ContextManager implements IMigrationManager{
     
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
         /**
           * There must exist at least one db context class
         * */
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

         $instance     = new $class_name();
         $raw_models   = $instance->get_models();
         $raw_fields   = $instance->get_model_fields();


         $clean_models = [];
         $clean_fields = [];
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
         return [$clean_models, $clean_fields];
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
         $pnamespace = $primary_model_instance->get_class_namespace();
         $fnamespace = $foreign_model_instance->get_class_namespace();
         $classname = $primary_model_instance->get_class_name().$foreign_model_instance->get_class_name();
         $pmodel_name = strtolower($primary_model_instance->get_class_name());
         $fmodel_name = strtolower($foreign_model_instance->get_class_name());
         $o_pmodel_name = $primary_model_instance->get_class_name();
         $o_fmodel_name = $foreign_model_instance->get_class_name();
         $pmodel_pk = $primary_model_instance->get_pk_name();
         $fmodel_pk = $foreign_model_instance->get_pk_name();

         $template = "<?php\n";
         $template .= "namespace ".$pnamespace.";\n\n";
         $template .= "use SaQle\\Dao\\Model\\Interfaces\\IThroughModel;\n";
         $template .= "use SaQle\\Dao\Field\\Types\\{Pk, OneToOne};\n";
         $template .= "use SaQle\\Dao\Field\\Interfaces\\IField;\n";
         $template .= "use SaQle\\Dao\\Model\\Dao;\n";
         $template .= "use ".$pnamespace."\\".$o_pmodel_name.";\n";
         $template .= "use ".$fnamespace."\\".$o_fmodel_name.";\n\n";
         $template .= "class {$classname} extends Dao implements IThroughModel{\n";
         /**
          * Declare the fields
          * */
         $template .= "\tpublic IField $"."id;\n";
         $template .= "\tpublic IField $".$pmodel_name.";\n";
         $template .= "\tpublic IField $".$fmodel_name.";\n\n";
         /**
          * Define the constructor
          * */
         $template .= "\tpublic function __construct(){\n";
         $template .= "\t\t$"."this->id = new Pk(type: PRIMARY_KEY_TYPE);\n";
         $template .= "\t\t$"."this->".$pmodel_name." = new OneToOne(fdao: ".$o_pmodel_name."::class, pk: '".$pmodel_pk."', fk: '".$pmodel_pk."', dname: '".$pmodel_pk."');\n";
         $template .= "\t\t$"."this->".$fmodel_name." = new OneToOne(fdao: ".$o_fmodel_name."::class, pk: '".$fmodel_pk."', fk: '".$fmodel_pk."', dname: '".$fmodel_pk."');\n";
         $template .= "\t\tparent::__construct();\n\n";
         $template .= "\t\t$"."this->set_meta([\n";
         $template .= "\t\t\t'auto_cmdt_fields' => true\n";
         $template .= "\t\t]);\n\n";
         $template .= "\t}\n\n";
         /**
          * Define get related models function
          * */
         $template .= "\tpublic static function get_related_models() : array{\n";
         $template .= "\t\treturn [\n";
         $template .= "\t\t\t".$o_pmodel_name."::class,\n";
         $template .= "\t\t\t".$o_fmodel_name."::class,\n";
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";

         $template .= "}\n";

         $path = $this->get_path_from_namespace($pnamespace, $project_root);
         $filename = $path."/".strtolower($classname).".php";

         file_put_contents($filename, $template);
         return $pnamespace."\\".$classname;
     }

     private function extract_model_fields($models, $project_root, &$manytomany_throughs){
         $model_fields = [];
         $through_models = [];
         foreach($models as $n => $m){
             if($this->is_model_defined($m, $project_root)){
                 $model_fields[$n] = [];
                 $mi = new $m();
                 $mfields = $mi->get_all_fields();
                 foreach($mfields as $mfn => $mfv){
                     $mfvdef = $mfv->get_field_definition();
                     if($mfvdef){
                         $model_fields[$n][$mfn] = ['field' => $mfv::class, 'params' => $mfv->get_kwargs(), 'def' => $mfvdef];
                     }

                     /**
                      * ManyToMany fields defined on respective tables will generate a new through table
                      * automatically. This is where that through table is determine. 
                      * */
                     if($mfv instanceof ManyToMany){
                         #get the relation object.
                         $relation = $mfv->get_relation();
                         #get the foreign model.
                         $fmodel = $relation->get_fdao();
                         /**
                          * 1. Foreign key model must have a ManyToMany field pointing to current table also defined on it.
                          * 2. Foreign key model must also be defined for the through table to be generated
                          * 3. The name of the through table will be generated by combining the two class names names.
                          * */
                         if($this->is_model_defined($fmodel, $project_root)){
                             $fmodel_instance = new $fmodel();
                             $relationship_field = $fmodel_instance->has_manytomany_relationship_with($m);
                             if($relationship_field !== false){
                                 $first_pointer = strtolower($mi->get_class_name().$fmodel_instance->get_class_name());
                                 $other_pointer = strtolower($fmodel_instance->get_class_name().$mi->get_class_name());
                                 if(!in_array($first_pointer, $manytomany_throughs) && !in_array($other_pointer, $manytomany_throughs)){
                                     array_push($manytomany_throughs, $first_pointer);
                                     array_push($manytomany_throughs, $other_pointer);
                                     $tm = $this->write_through_model($mi, $fmodel_instance, $project_root);
                                     $through_models[$first_pointer] = $tm;
                                 }
                             }
                         }
                     }
                 }
             }
         }
         return [$model_fields, $through_models];
     }

     private function write_database_snapshot($migration_name, $timestamp, $models, $dirname, $ctxname, $project_root){
         $class_name  = "{$ctxname}_{$timestamp}_{$migration_name}";
         $snap_folder = $dirname."/snapshots";
         $file_name   = $snap_folder."/".$class_name.".php";

         $models_template = "";
         $fields_template = "";
         foreach($models as $n => $m){
             if($this->is_model_defined($m, $project_root)){
                 $models_template .= "\t\t\t'".$n."' => '".$m."',\n";

                 $mi = new $m();
                 $mfields = $mi->get_all_fields();
                 $fields_template.= "\t\t\t'".$n."' => [\n";
                 foreach($mfields as $mfn => $mfv){
                     $fields_template .= "\t\t\t\t'".$mfn."' => [\n";
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

         $template = "<?php\n";
         $template .= "use SaQle\\Migration\\Base\\DbSnapshot;\n\n";
         $template .= "class {$class_name} extends DbSnapshot{\n";
         /**
          * Get the models.
          * */
         $template .= "\tpublic function get_models(){\n";
         $template .= "\t\treturn [\n";
         $template .= $models_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         /**
          * Get the model fields.
          * */
         $template .= "\tpublic function get_model_fields(){\n";
         $template .= "\t\treturn [\n";
         $template .= $fields_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         $template .= "}\n";

         //create migrations folder
         if(!file_exists($snap_folder)){
            mkdir($snap_folder);
         }

         file_put_contents($file_name, $template);
     }

     public function get_context_snapshot(...$options){
         $db_context     = $options['db_context'] ?? null;
         $project_root   = $options['project_root'] ?? null;
         $app_name       = $options['app_name'] ?? null;
         $migration_name = $options['migration_name'] ?? null;
         $timestamp      = $options['timestamp'] ?? null;
         $context_classes = $this->get_context_classes($db_context);
         $context_snapshot = [];
         $manytomany_throughs = [];
         foreach($context_classes as $ctx){
             $context_snapshot[$ctx] = [];
             $ctxparts = explode("\\", $ctx);
             $ctxname  = end($ctxparts);

             $models   = $ctx::get_models(); //Add a get defined models to db context to ensure that medels coming in are defined
             [$model_fields, $through_models] = $this->extract_model_fields($models, $project_root, $manytomany_throughs);
             $models = array_merge($models, $through_models);

             $a        = new \ReflectionClass($ctx);
             $filename = $a->getFileName();
             $dirname  = pathinfo($filename)['dirname'];

             $connection = (Cf::create(ContainerService::class))->createConnection(...
                 ['context' => (Cf::create(ContainerService::class))->createDbContextOptions(...DB_CONTEXT_CLASSES[$ctx])
             ]);

             $this->write_database_snapshot($migration_name, $timestamp, $models, $dirname, $ctxname, $project_root);

             $added_models    = $models;
             $removed_models  = [];

             $added_coulmns   = [];
             $removed_columns = [];

             try{
                 $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
                 $data = [DB_CONTEXT_CLASSES[$ctx]['name']];
                 $statement = $connection->execute($sql, $data)['statement'];
                 $object = $statement->fetchObject(); 

                 if($object){
                    /**
                     * Database exists, acquire the timestamp for the last snapshot.
                     * */
                     $last_migration = Migration::db()
                     ->order(fields: ['date_added'], direction: 'DESC')
                     ->limit(page: 1, records: 1)
                     ->first_or_default();
                     if($last_migration){

                         [$last_models, $last_model_fields] = $this->get_snapshot(
                            $last_migration->migration_name, 
                            $last_migration->migration_timestamp, 
                            $dirname, 
                            $ctxname,
                            $project_root
                         );

                         /**
                          * Which new models have been added.
                          * */
                         $added_models = array_diff($models, $last_models);

                         /**
                          * Which models have been removed
                          * */
                         $removed_models = array_diff($last_models, $models);

                         /**
                          * Which models have been maintained.
                          * */
                         $maintained_models = array_intersect($models, $last_models);
                         foreach($maintained_models as $table_name => $model_name){
                             $current_column_keys  = array_keys($model_fields[$table_name]);
                             $previous_column_keys = array_keys($last_model_fields[$table_name]);

                             $added_column_keys = array_diff($current_column_keys, $previous_column_keys);
                             $removed_column_keys = array_diff($previous_column_keys, $current_column_keys);

                             echo "Table: {$table_name}\n";
                             if($added_column_keys){
                                 $added_settings = ['name' => $table_name, 'model' => $model_name, 'columns' => []];
                                 foreach($added_column_keys as $ack){
                                     $added_settings['columns'][$ack] = $model_fields[$table_name][$ack]['def'];
                                 }
                                 $added_columns[] = $added_settings;
                             }
                             if($removed_column_keys){
                                 $removed_settings = ['name' => $table_name, 'model' => $model_name, 'columns' => []];
                                 foreach($removed_column_keys as $rck){
                                     $removed_settings['columns'][$rck] = $last_model_fields[$table_name][$rck]['def'];
                                 }
                                 $removed_columns[] = $removed_settings;
                             }
                         }
                     }
                 }
             }catch(\Exception $ignore){
                 
             }
             $context_snapshot[$ctx]['tables'] = [$added_models, $removed_models];
             $context_snapshot[$ctx]['columns'] = [$added_columns, $removed_columns];
         }
         return $context_snapshot;
     }
}
