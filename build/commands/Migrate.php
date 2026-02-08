<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Db;
use SaQle\Commons\FileUtils;
use SaQle\Core\Migration\Models\Migration;
use SaQle\Core\Migration\Tracker\MigrationTracker;
use SaQle\App;

class Migrate{
     use FileUtils;

     private static function process_migration_file($file, $migrations_folder, $project_root){
         $file_name = pathinfo($file, PATHINFO_FILENAME);
         $file_name_parts = explode("_", $file_name);
         $file_path = $migrations_folder."/".$file;
         if(!file_exists($file_path)){
             return;
         }

         cli_log("Scanning file {$file_name} for changes!\n");
         require_once $file_path;
         $class_instance = new $file_name();
         cli_log("Getting affected database contexts!\n");
         $touched_snapshots = $class_instance->snapshots();
         if(!$touched_snapshots){
             cli_log("No database context was affected by this migration! Exiting scan.\n");
             return;
         }

         cli_log("Affected contexts found!\n");
         print_r($touched_snapshots);
         
         $up_operations = $class_instance->up();

         foreach($touched_snapshots as $snapshot_name => $snapshot_location){
             self::process_context($snapshot_name, $snapshot_location, $file_name, $file_name_parts[2], $up_operations, $project_root);
         }
        
         return;
     }

     private static function create_table($op, $project_root, $dbdriver){
         $table_name = $op['params']['name'];
         $model_class = $op['params']['model'];

         cli_log("Attempting to create table: {$table_name}!\n");
         $tblcreated = $dbdriver->create_table_from_migration($table_name, $model_class);

         if(!$tblcreated){
             cli_log("Table {$table_name} creation failed!\n");
             return;
         }

         cli_log("Table {$table_name} created!\n");
         return;
     }

     private static function drop_table($op, $dbdriver){
         $table_name = $op['params']['name'];
         echo "Attempting to drop table: {$table_name}!\n";
         $tbldropped = $dbdriver->drop_table($table_name);

         if(!$tbldropped){
             echo "Table {$table_name} deletion failed!\n";
             return;
         }

         echo "Table {$table_name} deleted!\n";
         return;
     }

     private static function add_columns($op, $dbdriver){
         $table_name = $op['params']['name'];
         echo "Attempting to add new columns table: {$table_name}!\n";
         $colsadded = $dbdriver->add_columns($table_name, $op['params']['columns']);

         if(!$colsadded){
             echo "Columns addition to table {$table_name} failed!\n";
             return;
         }

         echo "New columns added to table {$table_name}!\n";
         return;
     }

     private static function drop_columns($op, $dbdriver){
         $table_name = $op['params']['name'];
         echo "Attempting to delete columns from table: {$table_name}!\n";
         $colsdropped = $dbdriver->drop_columns($table_name, $op['params']['columns']);

         if(!$colsdropped){
             echo "Column deletion from table {$table_name} failed!\n";
             return;
         }

         echo "Columns dropped from table {$table_name}!\n";
         return;
     }

     private static function add_unique($op, $dbdriver){
         $table_name = $op['params']['name'];
         $columns    = explode(",", $op['unique']);
         echo "Attempting to add unique fields to table: {$table_name}!\n";
         $uniqueadded = $dbdriver->add_unique_constraints($table_name, $columns, $op['unique_together']);

         if(!$uniqueadded){
             echo "Failed to set unique columns (".$op['unique'].") on table {$table_name}!\n";
             return;
         }

         echo "Unique columns (".$op['unique'].") set on table {$table_name}!\n";
         return;
     }

     private static function drop_unique($op, $dbdriver){
         $table_name = $op['params']['name'];
         $columns    = explode(",", $op['unique']);
         echo "Attempting to drop unique fields from table table: {$table_name}!\n";
         $uniqueadded = $dbdriver->drop_unique_constraints($table_name, $columns, $op['unique_together']);

         if(!$uniqueadded){
             echo "Failed to drop unique columns (".$op['unique'].") from table {$table_name}!\n";
             return;
         }

         echo "Unique columns (".$op['unique'].") dropped from table {$table_name}!\n";
         return;
     }

     private static function process_up_operation($op, $project_root, $dbdriver){
         return match($op['action']){
             'create_table' => self::create_table($op, $project_root, $dbdriver),
             'drop_table'   => self::drop_table($op, $dbdriver),
             'add_columns'  => self::add_columns($op, $dbdriver),
             'drop_columns' => self::drop_columns($op, $dbdriver),
             'add_unique'   => self::add_unique($op, $dbdriver),
             'drop_unique'  => self::drop_unique($op, $dbdriver),
         };
     }

     private static function extract_snapshot_field_definitions(array $schema, string $table): array {
         if (!isset($schema[$table])) {
             return [];
         }

         return array_map(fn($field) => $field['def'], $schema[$table]);
     }

     private static function process_context($snapshot_name, $snapshot_location, $file_name, $migration_name, $up_operations, $project_root){
         $snapshot_path = $snapshot_location['path'];
         $snapshot_class = $snapshot_location['name'];

         echo "Confirming connection: {$snapshot_name} is defined!\n";

         $defined_context = config('connections')[$snapshot_name] ?? null;
         if(!$defined_context){
             echo "Connection: {$snapshot_name} not defined! Exiting!.\n";
             return;
         }

         require_once $snapshot_path;
         $snapshot = new $snapshot_class();
        
         $databasename = config('connections')[$snapshot_name]['database'];
         echo "Connection: {$snapshot_name} found! Pinging database: {$databasename} for existance!\n";

         $dbdriver = Db::driver(connection: $snapshot_name);
         $isdbnew = false;
         if(!$dbdriver->check_database_exists()){
             echo "Database {$databasename} not found. Attempting to create database {$databasename}\n";
             if(!$dbdriver->create_database()){
                 echo "Database {$databasename} could not be created! Exiting!\n";
                 return; 
             }
             $isdbnew = true;
         }

         $dbdriver->connect_with_database();
         cli_log("We have connected!\n");

         cli_log("Creating migrations table!\n");
         $migration_field_defs = self::extract_snapshot_field_definitions($snapshot->get_model_fields(), 'migrations');
         $unique_constraint_defs = $dbdriver->get_unique_constraint_sqls($snapshot->get_unique_constraints()['migrations'] ?? []);
         $dbdriver->create_table_from_migration('migrations', $migration_field_defs, $unique_constraint_defs);

         //record this migration in db
         $fn_parts = explode("_", $file_name);
         $recorded = Migration::get()->where('migration_name__eq', $migration_name)->where('migration_timestamp__eq', $fn_parts[1])->first_or_default();
         if(!$recorded){
             $recorded = Migration::create(['migration_name' => $migration_name, 'migration_timestamp' => $fn_parts[1], 'is_migrated' => 0])->now();
         }

         if($recorded->is_migrated === 1)
            return;

         #Get and execute the up operations.
         $ctx_up_operations = $up_operations[$snapshot_name];

         //$op_results = array_map(fn($op) => self::process_up_operation($op, $project_root, $dbdriver), $ctx_up_operations);

         #mark current migration file as having been migrated
         Migration::update(['is_migrated' => 1])->where('migration_id', $recorded->migration_id)->now();

         return;
     }

     private function order_migration_filenames(array $file_names, bool $reverse = false){
         $files = [];
         foreach($file_names as $name){
             $name_parts = explode("_", $name);
             $files[$name_parts[1]] = $name;
         }

         /* Sort and return the array */
         $fn = $reverse ? 'krsort' : 'ksort';
         $fn($files);
         return $files;
     }
     
     public function execute(string $project_root){
         $migrations_folder      = $project_root."/databases/migrations";
         $migration_tracker_file = $project_root."/databases/migrationstracker.bin";
         $tracker                = $this->unserialize_from_file($migration_tracker_file);
         if(!$tracker){
             $tracker = new MigrationTracker();
         }

         $files                  = [];
         if(config('environment') === 'development'){
             $files              = $tracker ? $tracker->get_migration_files() : [];
         }

         if($files){
             $migration_files = array_filter($files, function($f){
                 return !$f->is_migrated;
             });
             $migration_file_names = array_column($migration_files, 'file');
         }else{
             $files = $this->scandir(path: $migrations_folder, exts: ['php']);
             $files = $this->order_migration_filenames($files);
             $migration_file_names = array_values($files);
         }

         cli_log("Starting migrations!\n");
         array_map(fn($file) => self::process_migration_file($file, $migrations_folder, $project_root), $migration_file_names);

         /*$tracker->set_migrated($migration_file_names);
         $this->serialize_to_file($migration_tracker_file, $tracker);*/
         return;
     }
}
