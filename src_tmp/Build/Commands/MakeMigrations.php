<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Migration\Tracker\MigrationTracker;
use SaQle\Commons\FileUtils;
use SaQle\Core\Migration\Models\Migration;
use SaQle\Build\Utils\MigrationUtils;
use SaQle\Core\Support\{
     Db, 
     Cli
};
use ReflectionClass;
use Exception;

class MakeMigrations {

     use FileUtils;

     private string $migrations_folder;
     private string $snapshots_folder;
     private string $schemas_folder;

     public function __construct(){
         $base_path = config('base_path');

         $this->migrations_folder = $base_path."/databases/migrations";
         $this->snapshots_folder  = $base_path."/databases/snapshots";
         $this->schemas_folder    = $base_path."/databases/schemas";
     }

     private function constraints_are_equal(array $a, array $b) : bool {
         if(count($a) !== count($b)){
             return false;
         }

         ksort($a);
         ksort($b);

         foreach($a as $key => $values){
             if(!array_key_exists($key, $b)){
                 return false;
             }

             sort($values);
             $otherValues = $b[$key];
             sort($otherValues);

             if($values !== $otherValues){
                 return false;
             }
         }

         return true;
     }

     private function get_model_operations($snapshot, $snapshot_records){
         $up = "";
         $down = "";
         foreach($snapshot as $sk => $sv){
             $added_models = $sv['tables'][0];
             $removed_models = $sv['tables'][1];
             $maintained_models = $sv['tables'][2];
             $added_columns = $sv['columns'][0] ?? [];
             $removed_columns = $sv['columns'][1] ?? [];
             $unique_constraints = $sv['unique'][0];
             $last_unique_constraints = $sv['unique'][1];

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

                 $table_unique_constraints = $unique_constraints[$an] ?? [];
                 $table_last_unique_constraints = $last_unique_constraints[$an] ?? [];

                 //something changed
                 if(!$this->constraints_are_equal($table_unique_constraints, $table_last_unique_constraints)){

                     $up .= "\t\t\t\t['action' => 'update_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], ";
                     $up .= "'unique' => [\n";
                     foreach($table_unique_constraints as $ccn => $ccfs){
                         $up .= "\t\t\t\t\t'".$ccn."' => [\n";
                         foreach($ccfs as $cuf){
                             $up .= "\t\t\t\t\t\t'".$cuf."',\n"; 
                         }
                         $up .= "\t\t\t\t\t],\n";
                     }
                     $up .= "\t\t\t\t],\n";
                     $up .= "\t\t\t\t'prev_unique' => [\n";
                     foreach($table_last_unique_constraints as $pcn => $pcfs){
                         $up .= "\t\t\t\t\t'".$pcn."' => [\n";
                         foreach($pcfs as $puf){
                             $up .= "\t\t\t\t\t\t'".$puf."',\n"; 
                         }
                         $up .= "\t\t\t\t\t],\n";
                     }
                     $up .= "\t\t\t\t]],\n";


                     $down .= "\t\t\t\t['action' => 'update_unique', 'params' => ['name' => '".$an."', 'model' => '".$am."'], ";
                     $down .= "'prev_unique' => [\n";
                     foreach($table_unique_constraints as $ccn => $ccfs){
                         $down .= "\t\t\t\t\t'".$ccn."' => [\n";
                         foreach($ccfs as $cuf){
                             $down .= "\t\t\t\t\t\t'".$cuf."',\n"; 
                         }
                         $down .= "\t\t\t\t\t],\n";
                     }
                     $down .= "\t\t\t\t],\n";
                     $down .= "\t\t\t\t'unique' => [\n";
                     foreach($table_last_unique_constraints as $pcn => $pcfs){
                         $down .= "\t\t\t\t\t'".$pcn."' => [\n";
                         foreach($pcfs as $puf){
                             $down .= "\t\t\t\t\t\t'".$puf."',\n"; 
                         }
                         $down .= "\t\t\t\t\t],\n";
                     }
                     $down .= "\t\t\t\t]],\n";
                 }  
             }

             $up .= "\t\t\t],\n";
             $down .= "\t\t\t],\n";
         }

         $touched = "";
         foreach($snapshot_records as $sn => $s_attrs){
             $touched .= "\t\t\t'".$sn."' => [\n";
             $touched .= "\t\t\t\t'path' => '".$s_attrs[0]."',\n";
             $touched .= "\t\t\t\t'name' => '".$s_attrs[1]."',\n";
             $touched .= "\t\t\t],\n";
         }

         return [$up, $down, $touched];
     }

     private function extract_model_fields($models, $dbdriver){
         $model_fields = [];
         foreach($models as $n => $m){
             $model_fields[$n] = []; //all the fields defined on the model.
             $mfields = $m::get_fields();

             foreach($mfields as $mfn => $mfv){
                 $mfvdef = $dbdriver->translate_field_definition($mfv->get_definition());

                 if($mfvdef){
                     $db_col_name = $mfv->get_column();
                     $model_fields[$n][$db_col_name] = ['field' => $mfv::class, 'params' => [], 'def' => $mfvdef];
                 }
             }
         }

         return $model_fields;
     }

     private function extract_unique_constraints($models){
         $unique_constraints = [];
         foreach($models as $n => $m){
             $mi = $m::make();
             $unique_constraints[$n] = $mi::get_unique_constraints();
         }

         return $unique_constraints;
     }

     private function extract_fk_constraints($models, $schema_class){

         $schema_instance = new $schema_class();
         $fk_constraints = [];

         foreach($models as $n => $m){
             $mi = $m::make();
             $constraints = $mi::get_fk_constraints();
             $updated_constraints = [];
             foreach($constraints as $col_name => $cons_items){
                 $updated_constraints[$col_name] = [
                     'ref_table'       => $schema_instance->get_table_for_model($cons_items['ref_model']),
                     'ref_col'         => $cons_items['ref_col'],
                     'delete_action'   => $cons_items['delete_action'],
                     'update_action'   => $cons_items['update_action'],
                     'local_field'     => $cons_items['local_field'],
                     'constraint_name' => "fk_{$n}_{$cons_items['local_field']}"
                 ];
             }

             $fk_constraints[$n] = $updated_constraints;
         }

         return $fk_constraints;
     }

     private function write_schema_snapshot($snapshot_class_name, $models, $unique_constraints, $fks_constraints, $dbdriver, $type){

         $destination_folder = path_join([$this->snapshots_folder, $type]);
         Cli::print($destination_folder);
         $file_name = path_join([$destination_folder, $snapshot_class_name.".php"]);

         $models_template = "";
         $fields_template = "";
         foreach($models as $n => $m){
             $models_template .= "\t\t\t'".$n."' => '".$m."',\n";
             $mfields = $m::get_fields();
             $fields_template.= "\t\t\t'".$n."' => [\n";
             foreach($mfields as $mfn => $mfv){
                 $db_col_name = $mfv->get_column();
                 $fields_template .= "\t\t\t\t'".$db_col_name."' => [\n";
                 $fields_template .= "\t\t\t\t\t'field' => '".$mfv::class."',\n";
                 $fields_template .= "\t\t\t\t\t'def' => '".$dbdriver->translate_field_definition($mfv->get_definition())."',\n";
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

         $uniques_template = "";
         foreach($unique_constraints as $n => $constraints){
             $uniques_template .= "\t\t\t'".$n."' => [\n";
             foreach($constraints as $constraint_name => $constraint_fields){
                 $uniques_template .= "\t\t\t\t'".$constraint_name."' => [\n";
                 foreach($constraint_fields as $uf){
                     $uniques_template .= "\t\t\t\t\t'".$uf."',\n"; 
                 }
                 $uniques_template .= "\t\t\t\t],\n";
             }
             $uniques_template .= "\t\t\t],\n";
         }

         $fks_template = "";
         foreach($fks_constraints as $n => $fk_constraints){
             $fks_template .= "\t\t\t'".$n."' => [\n";
             foreach($fk_constraints as $col_name => $con_items){
                 $fks_template .= "\t\t\t\t'".$col_name."' => [\n";
                 foreach($con_items as $item => $item_value){
                     $fks_template .= "\t\t\t\t\t'".$item."' => '".$item_value."',\n"; 
                 }
                 $fks_template .= "\t\t\t\t],\n";
             }
             $fks_template .= "\t\t\t],\n";
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
         $template .= "class {$snapshot_class_name} extends DbSnapshot{\n";
         
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
         $template .= "\tpublic function get_unique_constraints(){\n";
         $template .= "\t\treturn [\n";
         $template .= $uniques_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";

          //get fk fields.
         $template .= "\tpublic function get_fk_constraints(){\n";
         $template .= "\t\treturn [\n";
         $template .= $fks_template;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";

         $template .= "}\n";

         //create snapshot folder
         if(!is_dir($destination_folder)){
             saqle_dir()->create($destination_folder);
         }

         file_put_contents($file_name, $template);

         return $file_name;
     }

     private function get_snapshot($migration_name, $timestamp, $schema_class, $type){
         $class_name = "{$schema_class}_{$timestamp}_{$migration_name}";
         
         $file_name = path_join([$this->snapshots_folder, $type, $class_name.".php"]);
         
         require_once $file_name;

         $instance = new $class_name();
         $raw_models = $instance->get_models();
         $raw_fields = $instance->get_model_fields();
         $unique_constraints = $instance->get_unique_constraints();
         $fk_constraints = $instance->get_fk_constraints();

         $clean_models = [];
         $clean_fields = [];

         foreach($raw_models as $n => $m){
             $clean_models[$n] = $m;

             if(isset($raw_fields[$n]) && is_array($raw_fields[$n])){
                 $clean_fields[$n] = array_filter($raw_fields[$n], function($value, $key){
                     return $value['def'] !== '';
                 }, ARRAY_FILTER_USE_BOTH);
             }
         }

         return [$clean_models, $clean_fields, $unique_constraints, $fk_constraints];
     }

     private function get_schema_snapshot($connections, $timestamp, $migration_name, $type){

         $schema_snapshot = [];
         $snapshot_records = [];

         foreach($connections as $connection_name => $connection_config){

             Cli::print("Using connection: {$connection_name}");

             $connection_databases = $connection_config['databases'];

             foreach($connection_databases as $db_name => $db_schema){

                 $connection_key = $connection_name.".".$db_name;

                 $dbdriver = Db::using($connection_key)->driver();

                 $schema_snapshot[$connection_key] = [];
                 
                 $schema_class = MigrationUtils::get_class_name($db_schema);

                 //Acquire models registered in this schema
                 $models = new $db_schema()->get_permanent_models();

                 //Acquire model fields for models registered with db context.
                 $model_fields = $this->extract_model_fields($models, $dbdriver);

                 //acquire unique fields
                 $unique_constraints = $this->extract_unique_constraints($models);
                 $last_unique_constraints = [];

                 //acquire fk constraints
                 $fk_constraints = $this->extract_fk_constraints($models, $db_schema);
                 $last_fk_constraints = [];

                 $snapshot_class_name = "{$schema_class}_{$timestamp}_{$migration_name}";
                 $snapshot_path = $this->write_schema_snapshot(
                     $snapshot_class_name, 
                     $models, 
                     $unique_constraints, 
                     $fk_constraints, 
                     $dbdriver,
                     $type
                 );
                 $snapshot_records[$connection_key] = [$snapshot_path, $snapshot_class_name];

                 $added_models = $models;
                 $removed_models = [];
                 $maintained_models = [];

                 $added_columns = [];
                 $removed_columns = [];

                 try{
                     if(MigrationUtils::check_system_database(with_database: true)){
                         Cli::print("System database exists!");

                         //Database exists, acquire the timestamp for the last snapshot.
                         $last_migration = Migration::get()
                         ->order(fields: ['migration_timestamp'], direction: 'DESC')
                         ->limit(1)
                         ->first_or_null();

                         if($last_migration){

                             [$last_models, $last_model_fields, $last_unique_constraints, $last_fk_constraints] = $this->get_snapshot(
                                $last_migration->migration_name, 
                                $last_migration->migration_timestamp, 
                                $schema_class,
                                $type
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

                 $schema_snapshot[$connection_key]['tables'] = [$added_models, $removed_models, $maintained_models];
                 $schema_snapshot[$connection_key]['columns'] = [$added_columns, $removed_columns];
                 $schema_snapshot[$connection_key]['unique'] = [$unique_constraints, $last_unique_constraints];
                 $schema_snapshot[$connection_key]['fk'] = [$fk_constraints, $last_fk_constraints];
             }

         }

         return [$schema_snapshot, $snapshot_records];
     }

     private function make_migrations($type, $timestamp, $migration_name, $connections){
         
         $class_name = ucfirst($type).'_Migration_'.$timestamp.'_'.$migration_name;
         
         $destination_folder = path_join([$this->migrations_folder, $type]);
         $migration_filename = path_join([$destination_folder, $class_name.".php"]);

         Cli::print("Making {$migration_name} migrations now!\n");
         [$snapshot, $snapshot_records] = $this->get_schema_snapshot($connections, $timestamp, $migration_name, $type);

         [$up_models, $down_models, $touched_snapshots] = $this->get_model_operations($snapshot, $snapshot_records);
         
         $template = "<?php\n";
         $template .= "use SaQle\\Core\\Migration\\Base\\BaseMigration;\n\n";
         $template .= "class {$class_name} extends BaseMigration{\n";

         //get migration name
         $template .= "\tpublic function get_migration_name() : string {\n";
         $template .= "\t\treturn '".$migration_name."';\n";
         $template .= "\t}\n\n";

         //get migration timestamp
         $template .= "\tpublic function get_migration_timestamp() : int {\n";
         $template .= "\t\treturn '".$timestamp."';\n";
         $template .= "\t}\n\n";
         
         //Construct the touched_contexts method
         $template .= "\tpublic function snapshots() : array {\n";
         $template .= "\t\treturn [\n";
         $template .= $touched_snapshots;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         
         //Construct the up method.
         $template .= "\tpublic function up() : array {\n";
         $template .= "\t\treturn [\n";
         $template .= $up_models;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         
         //Construct the down method
         $template .= "\tpublic function down() : array {\n";
         $template .= "\t\treturn [\n";
         $template .= $down_models;
         $template .= "\t\t];\n";
         $template .= "\t}\n";
         $template .= "}\n";

         //create migrations folder
         if(!is_dir($destination_folder)){
             saqle_dir()->create($destination_folder);
         }

         if(file_put_contents($migration_filename, $template) !== false){
             Cli::print("Migration file created at: {$migration_filename}\n");
         }
     }

     public function execute($migration_name){

         $timestamp  = date('YmdHis');

         $connections = config('db.connections');

         $system_connection_names = [config('framework_connection')];

         $system_connections = array_intersect_key($connections, array_flip($system_connection_names));

         $tenant_connections = array_diff_key($connections, array_flip($system_connection_names));

         $this->make_migrations('system', $timestamp, $migration_name, $system_connections);
         $this->make_migrations('tenant', $timestamp, $migration_name, $tenant_connections);
     }
}
