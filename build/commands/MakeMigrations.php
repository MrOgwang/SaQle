<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Migration\Tracker\MigrationTracker;
use SaQle\Commons\FileUtils;
use SaQle\Core\Migration\Models\Migration;
use SaQle\Build\Utils\MigrationUtils;
use SaQle\Orm\Connection\Connection;
use ReflectionClass;
use Exception;

class MakeMigrations {
     use FileUtils;

     private function arrays_are_equal(array $a, array $b): bool {
         if(empty($a) && empty($b)){
             return true;
         }

         sort($a);
         sort($b);

         return $a === $b;
     }

     private function get_model_operations($snapshot){
         $up = "";
         $down = "";
         $touched = "";
         foreach($snapshot as $sk => $sv){
             $added_models = $sv['tables'][0];
             $removed_models = $sv['tables'][1];
             $maintained_models = $sv['tables'][2];
             $added_columns = $sv['columns'][0] ?? [];
             $removed_columns = $sv['columns'][1] ?? [];
             $unique_field_names = $sv['unique'][0];
             $last_unique_field_names = $sv['unique'][1];

             $up .= "\t\t\t'".$sk."' => [\n";
             $down .= "\t\t\t'".$sk."' => [\n";
             foreach($added_models as $an => $am){
                 $up .= "\t\t\t\t['action' => 'create_table', 'params' => ['name' => '".$an."', 'model' => '".$am."']],\n";
                 $down .= "\t\t\t\t['action' => 'drop_table', 'params' => ['name' => '".$an."', 'model' => '".$am."']],\n";
             }
             foreach($added_columns as $ac => $acv){
                 $columns_def = "";
                 foreach($acv['columns'] as $acdef_key => $acdef_val){
                    $columns_def .= "\t\t\t\t\t\t'".$acdef_key."' => '".$acdef_val."',\n";
                 }
                 $up .= "\t\t\t\t['action' => 'add_columns', 'params' => ['name' => '".$acv['name']."', 'model' => '".$acv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
                 $down .= "\t\t\t\t['action' => 'drop_columns', 'params' => ['name' => '".$acv['name']."', 'model' => '".$acv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
             }
             foreach($removed_models as $rn => $rm){
                 $up .= "\t\t\t\t['action' => 'drop_table', 'params' => ['name' => '".$rn."', 'model' => '".$rm."']],\n";
                 $down .= "\t\t\t\t['action' => 'create_table', 'params' => ['name' => '".$rn."', 'model' => '".$rm."']],\n";
             }
             foreach($removed_columns as $rc => $rcv){
                 $columns_def = "";
                 foreach($rcv['columns'] as $rcdef_key => $rcdef_val){
                    $columns_def .= "\t\t\t\t\t\t'".$rcdef_key."' => '".$rcdef_val."',\n";
                 }
                 $up .= "\t\t\t\t['action' => 'drop_columns', 'params' => ['name' => '".$rcv['name']."', 'model' => '".$rcv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
                 $down .= "\t\t\t\t['action' => 'add_columns', 'params' => ['name' => '".$rcv['name']."', 'model' => '".$rcv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
             }
             foreach($maintained_models as $an => $am){
                 $ut  = array_key_exists($an, $unique_field_names) ? ($unique_field_names[$an]['unique_together'] ? 'true' : 'false') : 'false';
                 $uf  = array_key_exists($an, $unique_field_names) ? $unique_field_names[$an]['fields'] : []; 
                 $last_ut = array_key_exists($an, $last_unique_field_names) ? ($last_unique_field_names[$an]['unique_together'] ? 'true' : 'false') : 'false';
                 $last_uf  = array_key_exists($an, $last_unique_field_names) ? $last_unique_field_names[$an]['fields'] : [];

                 //something has changed
                 if(!$this->arrays_are_equal($uf, $last_uf)){
                     if(!empty($last_uf)){
                         //first drop the previous unique fields
                         $up .= "\t\t\t\t['action' => 'drop_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $last_uf)."', 'unique_together' => ".$last_ut."],\n";
                         $down .= "\t\t\t\t['action' => 'add_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $last_uf)."', 'unique_together' => ".$last_ut."],\n";
                     }

                     if(!empty($uf)){
                         //add the new unique fields
                         $up .= "\t\t\t\t['action' => 'add_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $uf)."', 'unique_together' => ".$ut."],\n";
                         $down .= "\t\t\t\t['action' => 'drop_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $uf)."', 'unique_together' => ".$ut."],\n";
                     }
                 }elseif( (!empty($uf) && !empty($last_uf)) && $ut !== $last_ut){
                     //first drop the previous unique fields
                     $up .= "\t\t\t\t['action' => 'drop_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $last_uf)."', 'unique_together' => ".$last_ut."],\n";
                     $down .= "\t\t\t\t['action' => 'add_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $last_uf)."', 'unique_together' => ".$last_ut."],\n";
                     //add the new unique fields
                     $up .= "\t\t\t\t['action' => 'add_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $uf)."', 'unique_together' => ".$ut."],\n";
                     $down .= "\t\t\t\t['action' => 'drop_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], 'unique' => '".implode(',', $uf)."', 'unique_together' => ".$ut."],\n";
                 }
             }

             $up .= "\t\t\t],\n";
             $down .= "\t\t\t],\n";
             $touched = "\t\t\t'".$sk."',\n";
         }
         return [$up, $down, $touched];
     }

     private function extract_model_fields($models, $project_root){
         $model_fields = [];
         foreach($models as $n => $m){
             if(!MigrationUtils::is_model_defined($m, $project_root))
                 continue;

             $model_fields[$n] = []; //all the fields defined on the model.
             $mfields = $m::get_fields();

             foreach($mfields as $mfn => $mfv){
                 $mfvdef = $mfv->get_definition();

                 if($mfvdef){
                     $db_col_name = $mfv->get_column();
                     $model_fields[$n][$db_col_name] = ['field' => $mfv::class, 'params' => [], 'def' => $mfvdef];
                 }
             }
         }

         return $model_fields;
     }

     private function extract_unique_field_names($models, $project_root){
         $unique_field_names = [];
         foreach($models as $n => $m){
             if(!MigrationUtils::is_model_defined($m, $project_root))
                 continue;

             $mi = $m::make();
             $unique_field_names[$n] = ['unique_together' => $mi->is_unique_together(), 'fields' => $mi->get_unique_field_names()];
         }

         return $unique_field_names;
     }

     private function write_database_snapshot($migration_name, $timestamp, $models, $unique_field_names, $dirname, $ctxname, $project_root, $db_type){
         $class_name  = "{$ctxname}_{$timestamp}_{$migration_name}";
         $snap_folder = dirname($dirname)."/snapshots";
         $file_name   = $snap_folder."/".$class_name.".php";

         $models_template = "";
         $fields_template = "";
         foreach($models as $n => $m){
             if(MigrationUtils::is_model_defined($m, $project_root)){
                 $models_template .= "\t\t\t'".$n."' => '".$m."',\n";
                 $mfields = $m::get_fields();
                 $fields_template.= "\t\t\t'".$n."' => [\n";
                 foreach($mfields as $mfn => $mfv){
                     $db_col_name = $mfv->column_name;
                     $fields_template .= "\t\t\t\t'".$db_col_name."' => [\n";
                     $fields_template .= "\t\t\t\t\t'field' => '".$mfv::class."',\n";
                     //$fields_template .= "\t\t\t\t\t'def' => '".$mfv->get_definition()."',\n";
                     $fields_template .= "\t\t\t\t\t'params' => [\n"; 

                     $params = [];
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
         foreach($unique_field_names as $n => $u){
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
         $template .= "use SaQle\\Core\Migration\\Base\\DbSnapshot;\n\n";
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
         $template .= "\tpublic function get_unique_field_names(){\n";
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

     private function execute_pdo($pdo, $sql, $data = null){
         $statement = $pdo->prepare($sql);
         $response  = $statement->execute($data);
         return ['statement' => $statement, 'response' => $response];
     }

     private function get_snapshot($migration_name, $timestamp, $dirname, $ctxname, $project_root){
         $class_name  = "{$ctxname}_{$timestamp}_{$migration_name}";
         $snap_folder = dirname($dirname)."/snapshots";
         $file_name   = $snap_folder."/".$class_name.".php";
         if(!file_exists($file_name)){
            throw new Exception("The snapshot file({$class_name}) cannot be located!");
         }

         require_once $file_name;

         $instance      = new $class_name();
         $raw_models    = $instance->get_models();
         $raw_fields    = $instance->get_model_fields();
         $unique_field_names = $instance->get_unique_field_names();

         $clean_models   = [];
         $clean_fields   = [];

         foreach($raw_models as $n => $m){
             if(MigrationUtils::is_model_defined($m, $project_root)){
                 $clean_models[$n] = $m;

                 if(isset($raw_fields[$n]) && is_array($raw_fields[$n])){
                     $clean_fields[$n] = array_filter($raw_fields[$n], function($value, $key){
                         return $value['def'] !== '';
                     }, ARRAY_FILTER_USE_BOTH);
                 }
             }
         }

         return [$clean_models, $clean_fields, $unique_field_names];
     }

     private function get_context_snapshot($project_root, $app_name, $db_context, $timestamp, $migration_name, $tracker){

         $context_classes = MigrationUtils::get_context_classes($db_context);
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
             $unique_field_names = $this->extract_unique_field_names($models, $project_root);
             $last_unique_field_names = [];
             
             $a        = new ReflectionClass($ctx);
             $filename = $a->getFileName();
             $dirname  = pathinfo($filename)['dirname'];

             $connection_params = config('db_context_classes')[$ctx];
             $connection_params['name'] = ''; //we are connecting without a database, therefore set the database name to empty string
             $connection = resolve(Connection::class, $connection_params);

             $this->write_database_snapshot($migration_name, $timestamp, $models, $unique_field_names, $dirname, $ctxname, $project_root);

             /*$added_models    = $models;
             $removed_models  = [];
             $maintained_models = [];

             $added_columns   = [];
             $removed_columns = [];

             try{
                 $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
                 $data = [config('db_context_classes')[$ctx]['name']];
                 $statement = $this->execute_pdo($connection, $sql, $data)['statement'];
                 $object = $statement->fetchObject(); 

                 if($object){
                     //Database exists, acquire the timestamp for the last snapshot.
                     $last_migration = Migration::get()
                     ->order(fields: ['migration_timestamp'], direction: 'DESC')
                     ->limit(page: 1, records: 1)
                     ->first_or_default();
                     if($last_migration){

                         [$last_models, $last_model_fields, $last_unique_field_names] = $this->get_snapshot(
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
             }catch(Exception $ignore){
                 
             }

             $context_snapshot[$ctx]['tables'] = [$added_models, $removed_models, $maintained_models];
             $context_snapshot[$ctx]['columns'] = [$added_columns, $removed_columns];
             $context_snapshot[$ctx]['unique'] = [$unique_field_names, $last_unique_field_names];*/
         }

         return $context_snapshot;
     }

     public function execute($migration_name, $project_root, $app_name = null, $db_context = null){
         $timestamp  = date('YmdHis');
         $class_name = 'Migration_' . $timestamp . '_' . $migration_name;
         $migrations_folder = $project_root."/databases/migrations";
         $migration_filename = $migrations_folder."/".$class_name.".php";
         $migration_tracker_filename = $project_root."/databases/migrationstracker.bin";

         $tracker = $this->unserialize_from_file($migration_tracker_filename);
         if(!$tracker){
             $tracker = new MigrationTracker();
         }

         //check that the latest migration file has been migrated
         $current_migration_files = $tracker->get_migration_files();
         if($current_migration_files && $current_migration_files[ count($current_migration_files) - 1 ]->is_migrated === false){
             /*$fn = $current_migration_files[ count($current_migration_files) - 1 ]->file;
             echo "You have a pending migration file [$fn] that should be migrated first!\n";
             echo "Run command: php manage.php migrate\n";
             return;*/
         }

         echo "Making {$migration_name} migrations now!\n";
         $snapshot = $this->get_context_snapshot(...[
            'project_root'   => $project_root, 
            'app_name'       => $app_name, 
            'db_context'     => $db_context,
            'timestamp'      => $timestamp,
            'migration_name' => $migration_name,
            'tracker'        => $tracker
         ]);

         /*[$up_models, $down_models, $touched_contexts] = $this->get_model_operations($snapshot);
         
         $template = "<?php\n";
         $template .= "use SaQle\\Migration\\Base\\BaseMigration;\n\n";
         $template .= "class {$class_name} extends BaseMigration{\n";
         
         //Construct the touched_contexts method
         $template .= "\tpublic function touched_contexts(){\n";
         $template .= "\t\treturn [\n";
         $template .= $touched_contexts;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         
         //Construct the up method.
         $template .= "\tpublic function up(){\n";
         $template .= "\t\treturn [\n";
         $template .= $up_models;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         
         //Construct the down method
         $template .= "\tpublic function down(){\n";
         $template .= "\t\treturn [\n";
         $template .= $down_models;
         $template .= "\t\t];\n";
         $template .= "\t}\n";
         $template .= "}\n";

         //create migrations folder
         if(!file_exists($migrations_folder)){
             mkdir($migrations_folder);
         }

         if(file_put_contents($migration_filename, $template) !== false){
             echo "Migration created: {$migration_filename}\n";

             $tracker->add_migration((Object)['file' => $class_name.".php", 'is_migrated' => false]);
             $this->serialize_to_file($migration_tracker_filename, $tracker);
         }*/
     }
}
