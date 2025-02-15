<?php
namespace SaQle\Migration\Managers;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;
use SaQle\Services\Container\Cf;
use SaQle\Services\Container\ContainerService;
use SaQle\Migration\Models\Migration;
use SaQle\Commons\FileUtils;
use SaQle\Migration\Tracker\MigrationTracker;
use SaQle\Dao\Field\Types\{TextType, NumberType, FileField, OneToOne, ManyToMany, OneToMany, Pk};
use SaQle\Dao\Field\Types\Base\Relation;
use SaQle\Dao\Model\Interfaces\IThroughModel;

class ContextManager implements IMigrationManager{
     use FileUtils;

     private function get_class_namespace(string $long_class_name){
         $nameparts = explode("\\", $long_class_name);
         array_pop($nameparts);
         return implode("\\", $nameparts);
     }

     private function get_class_name(string $long_class_name){
         $nameparts = explode("\\", $long_class_name);
         $name = array_pop($nameparts);
         return $name;
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

     private function get_context_classes($db_context){ ///////////////////////////////////////////
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

         $instance      = new $class_name();
         $raw_models    = $instance->get_models();
         $raw_fields    = $instance->get_model_fields();
         $raw_t_models  = $instance->get_through_models();
         $raw_t_fields  = $instance->get_through_model_fields();


         $clean_models   = [];
         $clean_fields   = [];
         $clean_t_models = [];
         $clean_t_fields = [];

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

         foreach($raw_t_models as $n => $m){
             if($this->is_model_defined($m, $project_root)){
                 $clean_t_models[$n] = $m;

                 if(isset($raw_t_fields[$n]) && is_array($raw_t_fields[$n])){
                     $clean_t_fields[$n] = array_filter($raw_t_fields[$n], function($value, $key){
                         return $value['def'] !== '';
                     }, ARRAY_FILTER_USE_BOTH);
                 }
             }
         }

         return [$clean_models, $clean_fields, $clean_t_models, $clean_t_fields];
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

     private function write_typed_collection($model_class, $project_root){
         $primary_model_instance = $model_class::state();
         $p_model_dao_class      = $primary_model_instance->get_associated_model_class();
         $p_model_dao_namespace  = $this->get_class_namespace($p_model_dao_class);
         $p_model_dao_name       = $this->get_class_name($p_model_dao_class);
         $p_collection_namespace = $p_model_dao_namespace."\\Collections";
         $p_collection_name      = $p_model_dao_name."Collection";

         $ptemplate = "";
         $ptemplate .= "<?php\n";
         $ptemplate .= "/**\n";
         $ptemplate .= "* This is an auto generated file.\n"; 
         $ptemplate .= "*\n";
         $ptemplate .= "* The code here is designed to work as is, and must not be modified unless you know what you are doing.\n";
         $ptemplate .= "*\n";
         $ptemplate .= "* If you find ways that the code can be improved to enhance speed, efficiency or memory, be kind enough\n";
         $ptemplate .= "* to share with the author at wycliffomondiotieno@gmail.com or +254741142038. The author will not mind a cup\n";
         $ptemplate .= "* of coffee either.\n";
         $ptemplate .= "*\n";
         $ptemplate .= "* Commands to generate file:\n";
         $ptemplate .= "* 1. php manage.php make:migrations\n";
         $ptemplate .= "* 2. php manage.php make:collections\n";
         $ptemplate .= "* On your terminal, cd into project root and run the above commands\n";
         $ptemplate .= "* \n";
         $ptemplate .= "* A typed collection container for ".$p_model_dao_name."\n";
         $ptemplate .= "* */\n\n";
         $ptemplate .= "declare(strict_types=1);\n\n";
         $ptemplate .= "namespace ".$p_collection_namespace.";\n\n";
         $ptemplate .= "use ".$p_model_dao_class.";\n";
         $ptemplate .= "use SaQle\Dao\Model\Interfaces\ModelCollection;\n\n";
         $ptemplate .= "final class ".$p_collection_name." extends ModelCollection{\n";
         $ptemplate .= "\tprotected function type(): string{\n";
         $ptemplate .= "\t\treturn ".$p_model_dao_name."::class;\n";
         $ptemplate .= "\t}\n";
         $ptemplate .= "}\n";
         $ptemplate .= "?>\n";

         $ppath = $this->get_path_from_namespace($p_collection_namespace, $project_root);
         if(!file_exists($ppath)){
            mkdir($ppath, 0755);
         }

         $pfilename = $ppath."/".strtolower($p_collection_name).".php";
        
         file_put_contents($pfilename, $ptemplate);
     }

     private function write_through_model($primary_model_instance, $foreign_model_instance, $project_root){
         $pnamespace = $primary_model_instance->get_class_namespace();
         $througnamespace = $pnamespace."\\Throughs";
         $fnamespace = $foreign_model_instance->get_class_namespace();
         $classname = $primary_model_instance->get_class_name().$foreign_model_instance->get_class_name();
         $classname = str_replace("Schema", "", $classname)."Schema";

         $pmodel_name = strtolower($primary_model_instance->get_class_name());
         $pmodel_name = str_replace("schema", "", $pmodel_name);
         $fmodel_name = strtolower($foreign_model_instance->get_class_name());
         $fmodel_name = str_replace("schema", "", $fmodel_name);
         $o_pmodel_name = $primary_model_instance->get_class_name();
         $o_fmodel_name = $foreign_model_instance->get_class_name();
         $pmodel_pk = $primary_model_instance->get_pk_name();
         $fmodel_pk = $foreign_model_instance->get_pk_name();

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
         $template .= "use SaQle\\Dao\\Model\\Interfaces\\IThroughModel;\n";
         $template .= "use SaQle\\Dao\Field\\Types\\{Pk, OneToOne};\n";
         $template .= "use SaQle\\Dao\Field\\Interfaces\\IField;\n";
         $template .= "use SaQle\\Dao\\Model\\Schema\\TableSchema;\n";
         $template .= "use ".$pnamespace."\\".$o_pmodel_name.";\n";
         $template .= "use ".$fnamespace."\\".$o_fmodel_name.";\n";
         $template .= "use SaQle\\Core\\Assert\\Assert;\n\n";
         $template .= "class {$classname} extends TableSchema implements IThroughModel{\n";
         /**
          * Declare the fields
          * */
         $template .= "\tpublic IField $"."id;\n";
         $template .= "\tpublic IField $".$pmodel_name.";\n";
         $template .= "\tpublic IField $".$fmodel_name.";\n\n";
         $template .= "\tprivate static array $"."include_fields = [\n";
         $template .= "\t\t".$o_pmodel_name."::class => '".$fmodel_name."',\n";
         $template .= "\t\t".$o_fmodel_name."::class => '".$pmodel_name."'\n";
         $template .= "\t];\n\n";
         /**
          * Define the constructor
          * */
         $template .= "\tpublic function __construct(...$"."kwargs){\n";
         $template .= "\t\t$"."this->id = new Pk(type: PRIMARY_KEY_TYPE);\n";
         $template .= "\t\t$"."this->".$pmodel_name." = new OneToOne(fdao: ".$o_pmodel_name."::class, pk: '".$pmodel_pk."', fk: '".$pmodel_pk."', dname: '".$pmodel_pk."');\n";
         $template .= "\t\t$"."this->".$fmodel_name." = new OneToOne(fdao: ".$o_fmodel_name."::class, pk: '".$fmodel_pk."', fk: '".$fmodel_pk."', dname: '".$fmodel_pk."');\n\n";
         $template .= "\t\t$"."this->set_meta([\n";
         $template .= "\t\t\t'unique_fields' => ['".$pmodel_name."', '".$fmodel_name."'],\n";
         $template .= "\t\t\t'unique_together' => true\n";
         $template .= "\t\t]);\n\n";
         $template .= "\t\tparent::__construct(...$"."kwargs);\n\n";
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
         /**
          * Define get include field function.
          * */
         $template .= "\tpublic static function get_include_field($"."schema){\n";
         $template .= "\t\tAssert::keyExists(self::$"."include_fields, $"."schema);\n";
         $template .= "\t\treturn self::$"."include_fields[$"."schema];\n";
         $template .= "\t}\n";

         $template .= "}\n";

         $path = $this->get_path_from_namespace($througnamespace, $project_root);
         if(!file_exists($path)){
            mkdir($path, 0755);
         }
         $filename = $path."/".strtolower($classname).".php";

         file_put_contents($filename, $template);
         return $througnamespace."\\".$classname;
     }

     private function extract_model_fields($models, $project_root, &$manytomany_throughs, $ctx_class, $ctx_throughs, $generate_throughs = true){
         $model_fields = [];
         $through_models = [];
         foreach($models as $n => $m){
             if(!$this->is_model_defined($m, $project_root))
                 continue;

             $model_fields[$n] = []; //all the fields defined on the model.
             $mi = $m::state();
             $mfields = $mi->get_all_fields();
             foreach($mfields as $mfn => $mfv){
                 $mfvdef = $mfv->get_field_definition();
                 if($mfvdef){
                     $db_col_name = $mfv->get_db_column_name();
                     $model_fields[$n][$db_col_name] = ['field' => $mfv::class, 'params' => $mfv->get_kwargs(), 'def' => $mfvdef];
                 }

                 //ManyToMany fields defined on respective tables will generate a new through table
                 //automatically. This is where that through table is determined.
                 if($generate_throughs && $mfv instanceof ManyToMany){
                     echo "Generating throughs for {$mfn}!\n";
                     //get the foreign model.
                     $fmodel = $mfv->get_relation()->get_fdao();
                      //1. Foreign key model must have a ManyToMany field pointing to current table also defined on it.
                      //2. Foreign key model must also be defined for the through table to be generated
                      //3. The name of the through table will be generated by combining the two class names names.
                     if(!$this->is_model_defined($fmodel, $project_root))
                         continue;

                     echo "The foreign model {$fmodel} is defined\n";
                     $fmodel_instance = new $fmodel();
                     $relationship_field = $fmodel_instance->has_manytomany_relationship_with($m);

                     if($relationship_field === false)
                         continue;

                     echo "The foreign model {$fmodel} has a relationship with primary model: {$m}\n";
                     $first_pointer = strtolower($mi->get_class_name().$fmodel_instance->get_class_name());
                     $other_pointer = strtolower($fmodel_instance->get_class_name().$mi->get_class_name());
                     $first_pointer = str_replace("schema", "", $first_pointer)."schema";
                     $other_pointer = str_replace("schema", "", $other_pointer)."schema";

                     if(in_array($first_pointer, $manytomany_throughs) || in_array($other_pointer, $manytomany_throughs))
                         continue;

                     echo "A through field has not been generated for current run!\n";
                     //don't regenerate a through model that is already existing.
                     if(!array_key_exists($first_pointer, $ctx_throughs) && !array_key_exists($other_pointer, $ctx_throughs)){
                         echo "A through field doesn't exists in the records! Generating now!\n";
                         $tm = $this->write_through_model($mi, $fmodel_instance, $project_root);
                         $through_models[$first_pointer] = $tm;
                     }else{
                         echo "A through field exists in the records! Fetching now!\n";
                         if(isset($ctx_throughs[$first_pointer])){
                             $through_models[$first_pointer] = $ctx_throughs[$first_pointer];
                         }elseif(isset($ctx_throughs[$other_pointer])){
                             $through_models[$other_pointer] = $ctx_throughs[$other_pointer];
                         }
                     }
                     array_push($manytomany_throughs, $first_pointer);
                     array_push($manytomany_throughs, $other_pointer);
                 }
             }
         }
         return [$model_fields, $through_models];
     }

     private function write_database_snapshot($migration_name, $timestamp, $models, $through_models, $dirname, $ctxname, $project_root){
         $class_name  = "{$ctxname}_{$timestamp}_{$migration_name}";
         $snap_folder = $dirname."/snapshots";
         $file_name   = $snap_folder."/".$class_name.".php";

         $models_template = "";
         $fields_template = "";
         foreach($models as $n => $m){
             if($this->is_model_defined($m, $project_root)){
                 $models_template .= "\t\t\t'".$n."' => '".$m."',\n";

                 $mi = $m::state();
                 $mfields = $mi->get_all_fields();
                 $fields_template.= "\t\t\t'".$n."' => [\n";
                 foreach($mfields as $mfn => $mfv){
                     $db_col_name = $mfv->get_db_column_name();
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

         $through_models_template = "";
         $through_fields_template = "";
         foreach($through_models as $n => $m){
             if($this->is_model_defined($m, $project_root)){
                 $through_models_template .= "\t\t\t'".$n."' => '".$m."',\n";

                 $mi = $m::state();
                 $mfields = $mi->get_all_fields();
                 $through_fields_template .= "\t\t\t'".$n."' => [\n";
                 foreach($mfields as $mfn => $mfv){
                     $db_col_name = $mfv->get_db_column_name();
                     $through_fields_template .= "\t\t\t\t'".$db_col_name."' => [\n";
                     $through_fields_template .= "\t\t\t\t\t'field' => '".$mfv::class."',\n";
                     $through_fields_template .= "\t\t\t\t\t'def' => '".$mfv->get_field_definition()."',\n";
                     $through_fields_template .= "\t\t\t\t\t'params' => [\n"; 

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
                         $through_fields_template .= "\t\t\t\t\t\t'".(string)$pk."' => ".(string)$pvv.",\n";
                     }
                     $through_fields_template .= "\t\t\t\t\t],\n";
                     $through_fields_template .= "\t\t\t\t],\n";
                 }
                 $through_fields_template .= "\t\t\t],\n";
             }
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
         /**
          * Get the through models.
          * */
         $template .= "\tpublic function get_through_models(){\n";
         $template .= "\t\treturn [\n";
         $template .= $through_models_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         /**
          * Get the through model fields.
          * */
         $template .= "\tpublic function get_through_model_fields(){\n";
         $template .= "\t\treturn [\n";
         $template .= $through_fields_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";

         $template .= "}\n";

         //create migrations folder
         if(!file_exists($snap_folder)){
            mkdir($snap_folder);
         }

         file_put_contents($file_name, $template);
     }

     private function write_associated_dao($model_class, $project_root){
         $state = $model_class::state();
         $dao_class = $state->get_associated_model_class();
         $dao_namespace = $this->get_class_namespace($dao_class);
         $model_namespace = $state->get_class_namespace();
         $model_name = $state->get_class_name();
         $nameparts = explode("\\", $dao_class);
         $dao_name = end($nameparts);
         $fields = $state->get_all_fields();

         $props_template = "";
         $namaspaces = [];
         $toinitialize = [];
         foreach($fields as $fn => $fv){
             $fname = $state instanceof IThroughModel ? str_replace("schema", "", $fn) : $fn;
             $props_template .= "\tpublic $".$fname.";\n";

             /*if($fv instanceof TextType || $fv instanceof NumberType || $fv instanceof Pk){
                 $ptype = $fv->get_kwargs()['ptype'];
                 $props_template .= "\tpublic {$ptype} $".$fname.";\n";
             }elseif($fv instanceof Relation){
                 $fmodel = $fv->get_relation()->get_fdao();
                 $modelstate = $fmodel::state();
                 $daoclass = $modelstate->get_associated_model_class();
                 $daoparts = explode("\\", $daoclass);
                 $daoname = end($daoparts);
                 $daospace = $this->get_class_namespace($daoclass);

                 if($fv instanceof OneToMany || $fv instanceof ManyToMany){
                     $daoname = $daoname."Collection";
                     $daospace = $daospace."\\Collections";
                     $toinitialize[$fname] = $daoname;
                 }

                 if(!isset($namaspaces[$daospace])){
                     $namaspaces[$daospace] = [];
                 }
                 if(!in_array($daoname, $namaspaces[$daospace])){
                     $namaspaces[$daospace][] = $daoname;
                 }
                 $props_template .= "\tpublic {$daoname} $".$fname.";\n";
             }elseif($fv instanceof FileField){
                 $props_template .= "\tpublic string $".$fname.";\n";
             }*/
         }
         $props_template .= "\n\n";

         $namespace_template = "";
         foreach($namaspaces as $n => $daos){
             $namespace_template .= count($daos) > 1 ? "use ".$n."\\{".implode(", ", $daos)."};\n" : 
             "use ".$n."\\".implode(", ", $daos).";\n";
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
         $template .= "* 2. php manage.php make:models\n";
         $template .= "* On your terminal, cd into project root and run the above commands\n";
         $template .= "* \n";
         $template .= "* Models are generated behind the scense from table schemas defined by the user.\n";
         $template .= "* The model provides interfaces for interacting with the database.\n";
         $template .= "* */\n\n";
         $template .= "namespace ".$dao_namespace.";\n\n";
         $template .= "use ".$model_class.";\n";
         $template .= $namespace_template;
         $template .= "use SaQle\Dao\Model\Model;\n\n";
         $template .= "#[\AllowDynamicProperties]\n";
         $template .= "class {$dao_name} extends Model{\n\n";
         $template .= $props_template;
         $template .= "\tpublic function __construct(...$"."kwargs){\n";
         /*foreach($toinitialize as $n => $v){
             $template .= "\t\t$"."this->".$n." = new ".$v."();\n";
         }*/
         $template .= "\t\tparent::__construct(...$"."kwargs);\n";
         $template .= "\t}\n\n";
         $template .= "\tpublic static function get_schema(){\n";
         $template .= "\t\treturn {$model_name}::state();\n";
         $template .= "\t}\n\n";
         $template .= "}\n";
         $template .= "?>";

         $path = $this->get_path_from_namespace($dao_namespace, $project_root);
         if(!file_exists($path)){
            mkdir($path, 0755);
         }
         $filename = $path."/".strtolower($dao_name).".php";

         file_put_contents($filename, $template);
         return $dao_namespace."\\".$dao_name;
     }

     public function get_context_snapshot(...$options){
         $db_context     = $options['db_context'] ?? null;
         $project_root   = $options['project_root'] ?? null;
         $app_name       = $options['app_name'] ?? null;
         $migration_name = $options['migration_name'] ?? null;
         $timestamp      = $options['timestamp'] ?? null;

         $context_classes = $this->get_context_classes($db_context);
         $context_snapshot = [];
         $manytomany_throughs = []; //this array makes sure only one through model is generated for a pair of related manytomany models.
         $generated_throughs = []; //collects the generated through models for each database context class.

         $trackerfile = $project_root."/migrations/migrationstracker.bin";
         $tracker = $this->unserialize_from_file($trackerfile);
         if(!$tracker){
             $tracker = new MigrationTracker();
         }
         $last_throughs = $tracker->get_through_models();

         foreach($context_classes as $ctx){
             $context_snapshot[$ctx] = [];
             $ctxparts = explode("\\", $ctx);
             $ctxname  = end($ctxparts);
             $ctx_last_throughs = $last_throughs[$ctx] ?? [];

             //Acquire models registered with db context
             $models   = $ctx::get_models(); 
             
             //Acquire model fields for models registered with db context and at the same time generate through_models from those fields.
             [$model_fields, $through_models] = $this->extract_model_fields($models, $project_root, $manytomany_throughs, $ctx, $ctx_last_throughs);
             $generated_throughs[$ctx] = $through_models;
            
             //Then acquire model fields for the generated through_models.
             [$through_model_fields] = $this->extract_model_fields($through_models, $project_root, $manytomany_throughs, $ctx, $ctx_last_throughs, false);
             
             $a        = new \ReflectionClass($ctx);
             $filename = $a->getFileName();
             $dirname  = pathinfo($filename)['dirname'];

             $connection = (Cf::create(ContainerService::class))->createConnection(...['ctx' => $ctx, 'without_db' => true]);

             $this->write_database_snapshot($migration_name, $timestamp, $models, $through_models, $dirname, $ctxname, $project_root);

             $added_models    = array_merge($models, $through_models);
             $removed_models  = [];

             $added_columns   = [];
             $removed_columns = [];

             try{
                 $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
                 $data = [DB_CONTEXT_CLASSES[$ctx]['name']];
                 $statement = $connection->execute($sql, $data)['statement'];
                 $object = $statement->fetchObject(); 

                 if($object){
                     //Database exists, acquire the timestamp for the last snapshot.
                     $last_migration = Migration::db()
                     ->order(fields: ['migration_timestamp'], direction: 'DESC')
                     ->limit(page: 1, records: 1)
                     ->first_or_default();
                     if($last_migration){

                         [$last_models, $last_model_fields, $last_through_models, $last_through_model_fields] = $this->get_snapshot(
                            $last_migration->migration_name, 
                            $last_migration->migration_timestamp, 
                            $dirname, 
                            $ctxname,
                            $project_root
                         );

                         //Which new models have been added.
                         $added_models = array_merge(array_diff($models, $last_models), array_diff($through_models, $last_through_models));

                         //Which models have been removed
                         $removed_models = array_merge(array_diff($last_models, $models), array_diff($last_through_models, $through_models));

                         //Which models have been maintained.
                         $maintained_models = array_merge(array_intersect($models, $last_models), array_intersect($through_models, $last_through_models));
                         $all_model_fields = array_merge($model_fields, $through_model_fields);
                         $all_last_model_fields = array_merge($last_model_fields, $last_through_model_fields);

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

             //Create associated daos for added models.
             if($added_models){
                 foreach($added_models as $m){
                     $this->write_associated_dao($m, $project_root);
                     $this->write_typed_collection($m, $project_root);
                 }
             }
             $context_snapshot[$ctx]['tables'] = [$added_models, $removed_models];
             $context_snapshot[$ctx]['columns'] = [$added_columns, $removed_columns];
         }

         $tracker->set_through_models($generated_throughs);
         $this->serialize_to_file($trackerfile, $tracker);

         return $context_snapshot;
     }

     public function make_collections(string $project_root, $app_name = null, $db_context = null){
         $context_classes = $this->get_context_classes($db_context);
         foreach($context_classes as $ctx){
             $models   = $ctx::get_models(); 
             foreach($models as $table_name => $table_schema){
                 $this->write_typed_collection($table_schema, $project_root);
             }
         }
     }

     public function make_models(string $project_root, $app_name = null, $db_context = null){
         $context_classes = $this->get_context_classes($db_context);
         foreach($context_classes as $ctx){
             $models   = $ctx::get_models(); 
             foreach($models as $table_name => $table_schema){
                 $this->write_associated_dao($table_schema, $project_root);
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
             foreach($seeds as $seed){
                $model = $seed['model'];
                $file  = $path.DIRECTORY_SEPARATOR.$seed['file'];

                echo "Now seeding for model: {$model}\n";
                $data = require_once $file;
                $seeded_data = $model::db()->add_multiple($data)->save();
                echo "Model: {$model} seeded!\n\n";
             }
          }
     }

     public function make_throughs(string $project_root, $app_name = null, $db_context = null){
         $context_classes = $this->get_context_classes($db_context);
         $manytomany_throughs = []; //this array makes sure only one through model is generated for a pair of related manytomany models.
         $generated_throughs = []; //collects the generated through models for each database context class.

         $trackerfile = $project_root."/migrations/migrationstracker.bin";
         $tracker = $this->unserialize_from_file($trackerfile);
         if(!$tracker){
             $tracker = new MigrationTracker();
         }
         
         foreach($context_classes as $ctx){
             $models   = $ctx::get_models();
             [$model_fields, $through_models] = $this->extract_model_fields($models, $project_root, $manytomany_throughs, $ctx, []);
             $generated_throughs[$ctx] = $through_models;

             //create collections and models for throughs.
             foreach($through_models as $table_name => $table_schema){
                 $this->write_associated_dao($table_schema, $project_root);
                 $this->write_typed_collection($table_schema, $project_root);
             }

         }

         $tracker->set_through_models($generated_throughs);
         $this->serialize_to_file($trackerfile, $tracker);
     }

     public function make_superuser(string $project_root, $email, $password){
         $model_class_schema = AUTH_MODEL_CLASS;
         $model_class        = $model_class_schema::get_associated_model_class();
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
