<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Db;
use SaQle\Commons\FileUtils;
use SaQle\Core\Migration\Models\Migration;
use SaQle\Core\Migration\Tracker\MigrationTracker;
use SaQle\Build\Utils\MigrationUtils;

class Migrate{
     use FileUtils;

     private string $migrations_folder;
    
     public function __construct(){
         $base_path = config('base_path');

         $this->migrations_folder = $base_path."/databases/migrations";
     }

     private function process_migration_file($file){
         $file_name = pathinfo($file, PATHINFO_FILENAME);
         $file_path = $this->migrations_folder."/".$file;
         
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
         $up_operations = $class_instance->up();

         $migration_name = $class_instance->get_migration_name();
         $migration_timestamp = $class_instance->get_migration_timestamp();
         foreach($touched_snapshots as $snapshot_name => $snapshot_location){
             $this->process_snapshot(
                 $snapshot_name, 
                 $snapshot_location, 
                 $migration_name,
                 $migration_timestamp,
                 $up_operations
             );
         }
        
         return;
     }

     private function create_table($op, $dbdriver, $snapshot){
         $table_name = $op['params']['name'];
         $model_class = $op['params']['model'];

         cli_log("Attempting to create table: {$table_name}!\n");
         $migration_field_defs = $this->extract_snapshot_field_definitions($snapshot->get_model_fields(), $table_name);
         $unique_constraint_defs = $dbdriver->get_unique_constraint_sqls($snapshot->get_unique_constraints()[$table_name] ?? []);
         $tblcreated = $dbdriver->create_table_from_migration($table_name, $migration_field_defs, $unique_constraint_defs);

         if(!$tblcreated){
             cli_log("Table {$table_name} creation failed!\n");
             return;
         }

         cli_log("Table {$table_name} created!\n");
         return;
     }

     private function drop_table($op, $dbdriver){
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

     private function add_columns($op, $dbdriver){
         $table_name = $op['params']['name'];
         
         cli_log("Attempting to add new columns table: {$table_name}!\n");
         $colsadded = $dbdriver->add_columns($table_name, $op['params']['columns']);

         if(!$colsadded){
             cli_log("Columns addition to table {$table_name} failed!\n");
             return;
         }

         cli_log("New columns added to table {$table_name}!\n");
         return;
     }

     private function drop_columns($op, $dbdriver){
         $table_name = $op['params']['name'];
         
         cli_log("Attempting to delete columns from table: {$table_name}!\n");
         $colsdropped = $dbdriver->drop_columns($table_name, $op['params']['columns']);

         if(!$colsdropped){
             cli_log("Column deletion from table {$table_name} failed!\n");
             return;
         }

         cli_log("Columns dropped from table {$table_name}!\n");
         return;
     }

     private function update_unique($op, $dbdriver){
         $table_name = $op['params']['name'];

         cli_log("Attempting to update unique constraints on table: {$table_name}!\n");
         $dbdriver->add_unique_constraints($table_name, $op['unique'], $op['prev_unique']);

         return;
     }

     private function process_up_operation($op, $dbdriver, $snapshot){
         return match($op['action']){
             'create_table'  => $this->create_table($op, $dbdriver, $snapshot),
             'drop_table'    => $this->drop_table($op, $dbdriver),
             'add_columns'   => $this->add_columns($op, $dbdriver),
             'drop_columns'  => $this->drop_columns($op, $dbdriver),
             'update_unique' => $this->update_unique($op, $dbdriver)
         };
     }

     private function extract_snapshot_field_definitions(array $schema, string $table): array {
         if (!isset($schema[$table])) {
             return [];
         }

         return array_filter(array_map(fn($field) => $field['def'], $schema[$table]));
     }

     private function process_snapshot($snapshot_name, $snapshot_location, $migration_name, $migration_timestamp, $up_operations){
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
         $migration_field_defs = $this->extract_snapshot_field_definitions($snapshot->get_model_fields(), 'migrations');
         $unique_constraint_defs = $dbdriver->get_unique_constraint_sqls($snapshot->get_unique_constraints()['migrations'] ?? []);
         $dbdriver->create_table_from_migration('migrations', $migration_field_defs, $unique_constraint_defs);

         //record this migration in db
         $recorded = Migration::get()
         ->where('migration_name__eq', $migration_name)
         ->where('migration_timestamp__eq', $migration_timestamp)
         ->first_or_default();

         if(!$recorded){
             $recorded = Migration::create([
                 'migration_name' => $migration_name, 
                 'migration_timestamp' => $migration_timestamp, 
                 'is_migrated' => 0
             ])->now();
         }

         if($recorded->is_migrated === 1)
            return;

         /*$prev_snapshot = MigrationUtils::get_previous_snapshot(
             $snapshot_name, 
             $recorded->prev_migration_name,
             $recorded->prev_migration_timestamp,
             $this->migrations_folder
         );*/

         #Get and execute the up operations.
         $ctx_up_operations = $up_operations[$snapshot_name];

         foreach($ctx_up_operations as $op){
             $this->process_up_operation($op, $dbdriver, $snapshot);
         }

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
     
     public function execute(){
         $migration_tracker_file = config('base_path')."/databases/migrationstracker.bin";
         $tracker = $this->unserialize_from_file($migration_tracker_file);
         if(!$tracker){
             $tracker = new MigrationTracker();
         }

         $files = [];
         if(config('environment') === 'development'){
             $files = $tracker ? $tracker->get_migration_files() : [];
         }

         if($files){
             $migration_files = array_filter($files, function($f){
                 return !$f->is_migrated;
             });
             $migration_file_names = array_column($migration_files, 'file');
         }else{
             $files = $this->scandir(path: $this->migrations_folder, exts: ['php']);
             $files = $this->order_migration_filenames($files);
             $migration_file_names = array_values($files);
         }

         cli_log("Starting migrations!\n");
         foreach($migration_file_names as $migration_file){
             $this->process_migration_file($migration_file);
         }

         $tracker->set_migrated($migration_file_names);
         $this->serialize_to_file($migration_tracker_file, $tracker);
         return;
     }
}
