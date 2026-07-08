<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Db;
use SaQle\Commons\FileUtils;
use SaQle\Core\Migration\Models\{
     Migration,
     TenantMigration
};
use SaQle\Core\Migration\Tracker\MigrationTracker;
use SaQle\Build\Utils\MigrationUtils;
use SaQle\Core\Support\Cli;

class Migrate {
     use FileUtils;

     private string $migrations_folder;
    
     public function __construct(){
         $base_path = config('base_path');

         $this->migrations_folder = $base_path."/databases/migrations";
     }

     private function create_system_database() : array {
         $system_db = Db::get_system_db(); 
         $system_db_connection = $system_db[0].".".$system_db[1];

         $system_db_driver = Db::using($system_db_connection)->driver();
         if(!$system_db_driver->check_database_exists()){
             if(!$system_db_driver->create_database()){
                 return [false, false];
             }
         }

         return [$system_db_connection, $system_db_driver];
     }

     private function get_migration_record($type, $migration_name, $migration_timestamp, $tenant_id = null){
         
         $migrations_model = $tenant_id ? TenantMigration::class : Migration::class;
         
         $manager = $migrations_model::get()
         ->where('migration_name__eq', $migration_name)
         ->where('migration_timestamp__eq', $migration_timestamp)
         ->where('type__eq', $type);
         if($tenant_id){
             $manager->where('tenant_id__eq', $tenant_id);
         }
         
         $record = $manager->first_or_null();

         if(!$record){

             $migration_props = [
                 'migration_name'      => $migration_name, 
                 'migration_timestamp' => $migration_timestamp, 
                 'is_migrated'         => 0,
                 'type'                => $type
             ];

             if($tenant_id){
                 $migration_props['tenant_id'] = $tenant_id;
             }

             $record = $migrations_model::create($migration_props)->now();
         }

         return $record;
     }

     private function update_migration_record(string $migration_id, $tenant_id = null){

         $migrations_model = $tenant_id ? TenantMigration::class : Migration::class;

         $migrations_model::update(['is_migrated' => 1])->where('migration_id', $migration_id)->now();

     }

     private function create_migrations_table($system_snapshot, $system_db_driver){
         
         require $system_snapshot['path'];

         $system_snapshot = $system_snapshot['name'];

         $system_schema = new $system_snapshot();

         Cli::print("Creating migrations table!\n");
         $migration_field_defs = $this->extract_snapshot_field_definitions(
             $system_schema->get_model_fields(), 
             'migrations'
         );
         $unique_constraint_defs = $system_db_driver->get_unique_constraint_sqls(
             $system_schema->get_unique_constraints()['migrations'] ?? []
         );
         $system_db_driver->connect_with_database();
         $system_db_driver->create_table_from_migration(
             'migrations', 
             $migration_field_defs, 
             $unique_constraint_defs, 
             []
         );
     }

     private function process_migration_file($type, $file, array $tenants = [], bool $tenancy_enabled = false){
         
         $file_name = pathinfo($file, PATHINFO_FILENAME);
         $file_path =  path_join([$this->migrations_folder, $type, $file]);
         
         if(!file_exists($file_path)){
             return;
         }

         Cli::print("Scanning file {$file_name} for changes!\n");
         require_once $file_path;

         $class_instance = new $file_name();
         Cli::print("Getting affected database schemas!\n");

         $migration_name = $class_instance->get_migration_name();
         $migration_timestamp = $class_instance->get_migration_timestamp();

         $touched_snapshots = $class_instance->snapshots();
         if(!$touched_snapshots){
             Cli::print("No database schema was affected by this migration! Exiting scan.\n");
             return;
         }

         Cli::print("Affected schemas found!\n");

         //create the system db and the migrations table here!
         [$system_db_connection, $system_db_driver] = $this->create_system_database();
         if(!$system_db_connection || !$system_db_driver){
             return;
         }

         if($type === 'system'){
             $this->create_migrations_table($touched_snapshots[$system_db_connection], $system_db_driver);
         }

         //record this migration in db
         $migration_record = $this->get_migration_record($type, $migration_name, $migration_timestamp);
         
         if($migration_record->is_migrated === 1)
             return;

         $up_operations = $class_instance->up();

         if(!$tenants){

             foreach($touched_snapshots as $snapshot_name => $snapshot_location){
                 $this->process_snapshot(
                     $snapshot_name, 
                     $snapshot_location, 
                     $migration_name,
                     $migration_timestamp,
                     $up_operations
                 );
             }

         }else{

             foreach($tenants as $tenant){

                 //record tenant migration in db
                 $migration_record = $this->get_migration_record($type, $migration_name, $migration_timestamp, $tenant->tenant_id);

                 foreach($touched_snapshots as $snapshot_name => $snapshot_location){
                     $this->process_snapshot(
                         $snapshot_name, 
                         $snapshot_location, 
                         $migration_name,
                         $migration_timestamp,
                         $up_operations,
                         $tenant
                     );
                 }

                 //update tenant migration record
                 $this->update_migration_record($migration_record->migration_id, $tenant->tenant_id);

             }
         }

         //update migration to migrated
         $this->update_migration_record($migration_record->migration_id);

         return;
     }

     private function create_table($op, $dbdriver, $snapshot){
         $table_name = $op['params']['name'];
         $model_class = $op['params']['model'];

         Cli::print("Attempting to create table: {$table_name}!\n");
         $migration_field_defs = $this->extract_snapshot_field_definitions($snapshot->get_model_fields(), $table_name);
         $unique_constraint_defs = $dbdriver->get_unique_constraint_sqls($snapshot->get_unique_constraints()[$table_name] ?? []);
         $fk_constraint_defs = $dbdriver->get_fk_constraint_sqls($snapshot->get_fk_constraints()[$table_name] ?? []);
         $tblcreated = $dbdriver->create_table_from_migration($table_name, $migration_field_defs, $unique_constraint_defs, $fk_constraint_defs);

         if(!$tblcreated){
             Cli::print("Table {$table_name} creation failed!\n");
             return;
         }

         Cli::print("Table {$table_name} created!\n");
         return;
     }

     private function drop_table($op, $dbdriver){
         $table_name = $op['params']['name'];
         Cli::print("Attempting to drop table: {$table_name}!\n");
         $tbldropped = $dbdriver->drop_table($table_name);

         if(!$tbldropped){
             Cli::print("Table {$table_name} deletion failed!\n");
             return;
         }

         Cli::print("Table {$table_name} deleted!\n");
         return;
     }

     private function add_columns($op, $dbdriver){
         $table_name = $op['params']['name'];
         
         Cli::print("Attempting to add new columns table: {$table_name}!\n");
         $colsadded = $dbdriver->add_columns($table_name, $op['params']['columns']);

         if(!$colsadded){
             Cli::print("Columns addition to table {$table_name} failed!\n");
             return;
         }

         Cli::print("New columns added to table {$table_name}!\n");
         return;
     }

     private function drop_columns($op, $dbdriver){
         $table_name = $op['params']['name'];
         
         Cli::print("Attempting to delete columns from table: {$table_name}!\n");
         $colsdropped = $dbdriver->drop_columns($table_name, $op['params']['columns']);

         if(!$colsdropped){
             Cli::print("Column deletion from table {$table_name} failed!\n");
             return;
         }

         Cli::print("Columns dropped from table {$table_name}!\n");
         return;
     }

     private function update_unique($op, $dbdriver){
         $table_name = $op['params']['name'];

         Cli::print("Attempting to update unique constraints on table: {$table_name}!\n");
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

     private function process_snapshot(
         $snapshot_name, 
         $snapshot_location, 
         $migration_name, 
         $migration_timestamp, 
         $up_operations,
         $tenant = null
     ){
         $original_key = $snapshot_name;
         $snapshot_path = $snapshot_location['path'];
         $snapshot_class = $snapshot_location['name'];

         if($tenant && config('tenancy.enabled')){
             [$snapshot_name, $snapshot_schema] = Db::register_tenant_db($snapshot_name, $tenant);
         }

         Cli::print("Confirming connection: {$snapshot_name} is defined!\n");

         //confirm associated connection schema is defined
         Db::get_connection_schema($snapshot_name);

         require_once $snapshot_path;
         $snapshot = new $snapshot_class();
        
         $connection_parts = explode(".", $snapshot_name);
         $databasename = $connection_parts[1];
         Cli::print("Connection: {$snapshot_name} found! Pinging database: {$databasename} for existance!\n");

         $dbdriver = Db::using($snapshot_name)->driver();
         if(!$dbdriver->check_database_exists()){
             Cli::print("Database {$databasename} not found. Attempting to create database {$databasename}\n");
             if(!$dbdriver->create_database()){
                 Cli::print("Database {$databasename} could not be created! Exiting!\n");
                 return; 
             }
         } 

         $dbdriver->connect_with_database();
         Cli::print("We have connected!\n");

         //Get and execute the up operations.
         $ctx_up_operations = $up_operations[$original_key];

         foreach($ctx_up_operations as $op){
             $this->process_up_operation($op, $dbdriver, $snapshot);
         }
 
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
     
     private function migrate($type, $tenancy_enabled, $tenant_model){

         $destination_folder = path_join([$this->migrations_folder, $type]);
         $files = $this->scandir(path: $destination_folder, exts: ['php']);
         $files = $this->order_migration_filenames($files);
         $migration_file_names = array_values($files);

         Cli::print("Starting migrations!\n");

         $tenants = $type === 'tenant' ? $tenant_model::get()->all()->items() : [];
         foreach($migration_file_names as $migration_file){
             $this->process_migration_file($type, $migration_file, $tenants, $tenancy_enabled);
         }
 
         if($type === 'system' && !$tenancy_enabled){
             $latest_tenant = $tenant_model::get()
             ->order(fields: ['created_at'], direction: 'DESC')
             ->limit(1)
             ->first_or_null();

             if(!$latest_tenant){
                 $tenant_model::create([
                     'tenant_name' => config('app.name')
                 ])->now();
             }
         } 
     }

     public function execute(){

         $tenancy_enabled = config('tenancy.enabled', false);
         $tenant_model = config('tenancy.model_class');

         $this->migrate('system', $tenancy_enabled, $tenant_model);
         $this->migrate('tenant', $tenancy_enabled, $tenant_model);

         return;
     }
}
