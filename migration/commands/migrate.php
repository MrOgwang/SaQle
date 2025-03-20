<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;
use SaQle\Orm\Database\Manager\DbManagerFactory;
use SaQle\Commons\FileUtils;
use SaQle\Migration\Models\Migration;
use SaQle\Migration\Tracker\MigrationTracker;

class Migrate{
     use FileUtils;
     public function __construct(private IMigrationManager $manager){

     }

     private static function process_migration_file($file, $migrations_folder, $project_root){
         $file_name = pathinfo($file, PATHINFO_FILENAME);
         $file_name_parts = explode("_", $file_name);
         $file_path = $migrations_folder."/".$file;
         if(!file_exists($file_path)){
             return;
         }

         echo "Scanning file {$file_name} for changes!\n";
         require_once $file_path;
         $class_instance = new $file_name();
         echo "Getting affected database contexts!\n";
         $touched_contexts = $class_instance->touched_contexts();
         if(!$touched_contexts){
             echo "No database context was affected by this migration! Exiting scan.\n";
             return;
         }

         echo "Affected contexts found!\n";
         print_r($touched_contexts);
         
         $up_operations = $class_instance->up();
         $ctx_results = array_map(fn($ctx) => self::process_context($ctx, $file_name, $file_name_parts[2], $up_operations, $project_root), $touched_contexts);

         return;
     }

     private static function create_table($op, $project_root, $dbmanager){
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
             echo "The schema: {$model_class} for table: {$table_name} has not been defined. Jumping over!\n";
             return;
         }

         echo "The schema: {$model_class} for table: {$table_name} exists!\n";
         $tblcreated = $dbmanager->create_table($table_name, $model_class);

         if(!$tblcreated){
             echo "Table {$table_name} creation failed!\n";
             return;
         }

         echo "Table {$table_name} created!\n";
         return;
     }

     private static function drop_table($op, $dbmanager){
         $table_name = $op['params']['name'];
         echo "Attempting to drop table: {$table_name}!\n";
         $tbldropped = $dbmanager->drop_table($table_name);

         if(!$tbldropped){
             echo "Table {$table_name} deletion failed!\n";
             return;
         }

         echo "Table {$table_name} deleted!\n";
         return;
     }

     private static function add_columns($op, $dbmanager){
         $table_name = $op['params']['name'];
         echo "Attempting to add new columns table: {$table_name}!\n";
         $colsadded = $dbmanager->add_columns($table_name, $op['params']['columns']);

         if(!$colsadded){
             echo "Columns addition to table {$table_name} failed!\n";
             return;
         }

         echo "New columns added to table {$table_name}!\n";
         return;
     }

     private static function drop_columns($op, $dbmanager){
         $table_name = $op['params']['name'];
         echo "Attempting to delete columns from table: {$table_name}!\n";
         $colsdropped = $dbmanager->drop_columns($table_name, $op['params']['columns']);

         if(!$colsdropped){
             echo "Column deletion from table {$table_name} failed!\n";
             return;
         }

         echo "Columns dropped from table {$table_name}!\n";
         return;
     }

     private static function add_unique($op, $dbmanager){
         $table_name = $op['params']['name'];
         $columns    = explode(",", $op['unique']);
         echo "Attempting to add unique fields to table: {$table_name}!\n";
         $uniqueadded = $dbmanager->add_unique_columns($table_name, $columns, $op['unique_together']);

         if(!$uniqueadded){
             echo "Failed to set unique columns (".$op['unique'].") on table {$table_name}!\n";
             return;
         }

         echo "Unique columns (".$op['unique'].") set on table {$table_name}!\n";
         return;
     }

     private static function drop_unique($op, $dbmanager){
         $table_name = $op['params']['name'];
         $columns    = explode(",", $op['unique']);
         echo "Attempting to drop unique fields from table table: {$table_name}!\n";
         $uniqueadded = $dbmanager->drop_unique_columns($table_name, $columns, $op['unique_together']);

         if(!$uniqueadded){
             echo "Failed to drop unique columns (".$op['unique'].") from table {$table_name}!\n";
             return;
         }

         echo "Unique columns (".$op['unique'].") dropped from table {$table_name}!\n";
         return;
     }

     private static function process_up_operation($op, $project_root, $dbmanager){
         return match($op['action']){
             'create_table' => self::create_table($op, $project_root, $dbmanager),
             'drop_table'   => self::drop_table($op, $dbmanager),
             'add_columns'  => self::add_columns($op, $dbmanager),
             'drop_columns' => self::drop_columns($op, $dbmanager),
             'add_unique'   => self::add_unique($op, $dbmanager),
             'drop_unique'  => self::drop_unique($op, $dbmanager),
         };
     }

     private static function process_context($ctx, $file_name, $migration_name, $up_operations, $project_root){
         echo "Confirming context: {$ctx} is defined!\n";

         $defined_context = DB_CONTEXT_CLASSES[$ctx] ?? null;
         if(!$defined_context){
             echo "Context: {$ctx} not defined! Exiting!.\n";
             return;
         }
        
         $defined_context['ctx'] = $ctx;
         $databasename = DB_CONTEXT_CLASSES[$ctx]['name'];
         echo "Context: {$ctx} found! Pinging database: {$databasename} for existance!\n";

         $dbmanager = (new DbManagerFactory(...$defined_context))->manager();
         $isdbnew = false;
         if(!$dbmanager->check_database_exists($ctx)){
             echo "Database {$databasename} not found. Attempting to create database {$databasename}\n";
             if(!$dbmanager->create_database()){
                 echo "Database {$databasename} could not be created! Exiting!\n";
                 return; 
             }
             $isdbnew = true;
         }

         $dbmanager->connect();
         echo "We have connected!\n";

         if($isdbnew){
             echo "Creating migrations table!\n";
             $dbmanager->create_table('migrations', Migration::class);
         }

         //record this migration in db
         $fn_parts = explode("_", $file_name);
         $recorded = Migration::new(['migration_name' => $migration_name, 'migration_timestamp' => $fn_parts[1], 'is_migrated' => 0])->save();

         #Get and execute the up operations.
         $ctx_up_operations = $up_operations[$ctx];

         $op_results = array_map(fn($op) => self::process_up_operation($op, $project_root, $dbmanager), $ctx_up_operations);

         #mark current migration file as having been migrated
         Migration::set(['is_migrated' => 1])->where('migration_id', $recorded->migration_id)->update();

         return;
     }
     
     public function execute(string $project_root){
         $migrations_folder      = $project_root."/migrations";
         $migration_tracker_file = $project_root."/migrations/migrationstracker.bin";
         $tracker                = $this->unserialize_from_file($migration_tracker_file);
         $files                  = $tracker ? $tracker->get_migration_files() : [];
         if($files){
             $migration_files = array_filter($files, function($f){
                 return !$f->is_migrated;
             });
             $migration_file_names = array_column($migration_files, 'file');
         }else{
             $files = $this->scandir_chrono(path: $migrations_folder, reverse: false, exts: ['php']);
             $migration_file_names = array_values($files);
         }

         echo "Starting migrations!\n";
         array_map(fn($file) => self::process_migration_file($file, $migrations_folder, $project_root), $migration_file_names);

         $tracker->set_migrated($migration_file_names);
         $this->serialize_to_file($migration_tracker_file, $tracker);
         return;
     }
}
