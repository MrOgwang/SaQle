<?php
namespace SaQle\Migration\Commands;

use SaQle\Dao\DbContext\Manager\DbManagerFactory;
use SaQle\Commons\FileUtils;
use SaQle\Migration\Models\Migration;

class Migrate{
     use FileUtils;
     private function run_operation(){

     }

     public function execute(string $project_root){
         $migrations_folder = $project_root."/migrations";
         $migration_files   = $this->scandir_chrono(path: $migrations_folder, reverse: false, exts: ['php']);
         $mfc               = count($migration_files);
         echo "Starting migrations!\n";
         for($m = 0; $m < $mfc; $m++){
             $file = array_values($migration_files)[$m];
             $name = pathinfo($file, PATHINFO_FILENAME);
             $nameparts = explode("_", $name);

             echo "Scanning file {$name} for changes!\n";
             require_once $migrations_folder."/".$file;
             $class_instance = new $name();
             echo "Getting affected database contexts!\n";

             $touched_contexts = $class_instance->touched_contexts();
             if($touched_contexts){
                 echo "Affected contexts found!\n";
                 print_r($touched_contexts);
                 $tcc = count($touched_contexts);
                 for($c = 0; $c < $tcc; $c++){
                     $ctx = $touched_contexts[$c];
                     echo "Confirming context: {$ctx} is defined!\n";
                     $defined_context = DB_CONTEXT_CLASSES[$ctx] ?? null;
                     if(!$defined_context){
                        echo "Context: {$ctx} not defined! Exiting scan.\n";
                        continue;
                     }

                     $databasename = DB_CONTEXT_CLASSES[$ctx]['name'];
                     echo "Context: {$ctx} found! Pinging database: {$databasename} for existance!\n";

                     $dbmanager = (new DbManagerFactory(...$defined_context))->manager();
                     $iscreated = false;
                     if($dbmanager->check_database_exists($ctx)){
                         $iscreated = true;
                         echo "Database {$databasename} found!\n";

                         /**
                          * Check that current migrations were migrated.
                          * */
                         $exists = Migration::db()
                         ->order(fields: ['date_added'], direction: 'DESC')
                         ->limit(page: 1, records: 1)
                         ->where('migration_name__eq', $nameparts[2])
                         ->first_or_default();
                         if($exists){
                            echo "Migration {$name} has been committed!\n";
                            continue 2;
                         }
                     }else{
                         echo "Database {$databasename} not found. Attempting to create database {$databasename}\n";
                         $iscreated = $dbmanager->create_database();
                     }

                     if(!$iscreated){
                        echo "Database {$databasename} was not found and could not be created! Exiting!\n";
                        continue;
                     }
                     $dbmanager->create_table('migrations', Migration::class);

                     /**
                      * Get and execute the up operations.
                      * */
                     $up_operations = $class_instance->up()[$ctx];
                     foreach($up_operations as $op){
                        switch($op['action']){
                            case "create_table":
                                 $table_name = $op['params']['name'];
                                 echo "Attempting to create table: {$table_name}!\n";
                                 $model_class = $op['params']['model'];
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

                                 if(!file_exists($model_file_path)){
                                     echo "The model: {$model_class} for table: {$table_name} has not been defined. Jumping over!\n";
                                 }else{
                                     echo "The model: {$model_class} for table: {$table_name} exists!\n";
                                     $tblcreated = $dbmanager->create_table($table_name, $model_class);
                                     if($tblcreated){
                                        echo "Table {$table_name} created!\n";
                                     }else{
                                        echo "Table {$table_name} creation failed!\n";
                                     }
                                 }  
                            break;
                            case "drop_table":
                                 $table_name = $op['params']['name'];
                                 echo "Attempting to drop table: {$table_name}!\n";
                                 $tbldropped = $dbmanager->drop_table($table_name);
                                 if($tbldropped){
                                    echo "Table {$table_name} deleted!\n";
                                 }else{
                                    echo "Table {$table_name} deletion failed!\n";
                                 }
                            break;
                            case "add_columns":
                                 $table_name = $op['params']['name'];
                                 echo "Attempting to add new columns table: {$table_name}!\n";
                                 $colsadded = $dbmanager->add_columns($table_name, $op['params']['columns']);
                                 if($colsadded){
                                    echo "New columns added to table {$table_name}!\n";
                                 }else{
                                    echo "Columns addition to table {$table_name} failed!\n";
                                 }
                            break;
                            case "drop_columns":
                                 $table_name = $op['params']['name'];
                                 echo "Attempting to delete columns from table: {$table_name}!\n";
                                 $colsdropped = $dbmanager->drop_columns($table_name, $op['params']['columns']);
                                 if($colsdropped){
                                    echo "Columns dropped from table {$table_name}!\n";
                                 }else{
                                    echo "Column deletion from table {$table_name} failed!\n";
                                 }
                            break;
                        }
                     }
                 }

                 //record this migration in the migrations table.
                 $record = Migration::db()->add([
                    'migration_name' => $nameparts[2],
                    'migration_timestamp' => $nameparts[1],
                    'is_migrated' => 1
                 ])->save();

             }else{
                echo "No database context was affected by this migration! Exiting scan.\n";
                continue;
             }
         }
     }
}
