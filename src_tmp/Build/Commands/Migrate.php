<?php

namespace SaQle\Build\Commands;

use SaQle\Core\Support\{
     Db,
     Cli
};
use SaQle\Commons\File;
use SaQle\Core\Migration\Models\{
     Migration,
     TenantMigration
};
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;
use SaQle\Core\Migration\Operations\OperationFactory;

/**
 * Migration runner.
 *
 * Responsibilities:
 *
 * 1. Ensure the system database exists.
 * 2. Ensure migration tracking exists on the system database.
 * 3. Execute system migrations once.
 * 4. Execute application migrations:
 *      - once on all application DBs when tenancy is disabled
 *      - once per tenant DB when tenancy is enabled
 *
 * Migration files describe LOGICAL databases.
 *
 * The migration runner resolves those logical databases into
 * physical migration targets.
 */

class Migrate extends Command {

     private string $migrations_folder;

     private ?bool $first_migration = null;
    
     public function __construct(){
         $base_path = config('base_path');

         $this->migrations_folder = $base_path."/src/Databases/Migrations";
     }

     public function signature(): Signature {
         return Signature::make();
     } 

     private function sort_files(array $file_names, bool $reverse = false){

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

     private function fetch_migration($name, $timestamp, $connection, $database, $tenant_id = null){

         $model = $tenant_id ? TenantMigration::class : Migration::class;
         
         $manager = $model::using(system_connection())->get()
         ->where('migration_name__eq', $name)
         ->where('migration_timestamp__eq', $timestamp)
         ->where('connection__eq', $connection)
         ->where('database__eq', $database);
         
         if($tenant_id){
             $manager->where('tenant_id__eq', $tenant_id);
         }
         
         return $manager->first_or_fail();
     }

     private function add_migration($name, $timestamp, $connection, $database, $migrated = 0, $tenant_id = null){

         $model = $tenant_id ? TenantMigration::class : Migration::class;
         
         $migration = [
             'migration_name'      => $name, 
             'migration_timestamp' => $timestamp, 
             'is_migrated'         => $migrated,
             'connection'          => $connection,
             'database'            => $database
         ];

         if($tenant_id){
             $migration['tenant_id'] = $tenant_id;
         }

         return $model::using(system_connection())->create($migration)->now();
     }

     private function update_migration(string $migration_id, $tenant_id = null){

         $model = $tenant_id ? TenantMigration::class : Migration::class;

         return $model::using(system_connection())->update(['is_migrated' => 1])->where('migration_id', $migration_id)->now();
     }

     private function process_snapshot(
         $snapshot_key, 
         $snapshot_location, 
         $migration_name, 
         $migration_timestamp, 
         $up_operations,
         $tenant = null
     ){
         $snapshot_parts = explode(".", $snapshot_key);
         $snapshot_path = $snapshot_location['path'];
         $snapshot_class = $snapshot_location['name'];
         $connection = $snapshot_parts[0];
         $database = $snapshot_parts[1];

         //confirm snapshot schema exists
         Db::get_connection_schema(connection_key: $snapshot_key);

         //load snapshot
         require_once $snapshot_path;
         $snapshot = new $snapshot_class();

         //get database driver
         $dbdriver = Db::using($snapshot_key)->driver();

         //ensure database exists
         if(!$dbdriver->check_database_exists()){

             if($snapshot_key === "default.system"){
                 $this->first_migration = true;
             }

             if(!$dbdriver->create_database()){
                 return; 
             }
         }  

         //connect to the database
         $dbdriver->connect_with_database();

         $record = null;
         if(!$this->first_migration){
 
             $record = $this->fetch_migration(
                 $migration_name, $migration_timestamp, $connection, $database, $tenant ? $tenant->tenant_id : null
             );
         
             if($record->is_migrated === 1){
                 return;
             }
         }
         
         //execute up operations.
         foreach($up_operations as $op){
             $operation = OperationFactory::make($op['action']);
             $operation->execute($op, $dbdriver, $snapshot);
         }

         //update migration record
         $record = $this->first_migration ? 
         $this->add_migration(
             $migration_name, $migration_timestamp, $connection, $database, 1, $tenant ? $tenant->tenant_id : null
         ) :
         $this->update_migration($record->migration_id, $tenant ? $tenant->tenant_id : null);
 
         return;
     }

     private function process_file($file, $index){
         
         $name = pathinfo($file, PATHINFO_FILENAME);
         $path =  path_join([$this->migrations_folder, $file]);
         
         if(!file_exists($path)){
             return;
         }

         Cli::print("Scanning file: {$file}\n");

         require_once $path;

         $migration = new $name();

         Cli::print("Getting affected database schemas!\n");

         $snapshots = $migration->snapshots();

         if(!$snapshots){
             Cli::print("No database schema was affected by this migration! Exiting scan.\n");
             return;
         }

         Cli::print("Processing migration changes...!\n");

         //Run system migrations first
         $this->process_snapshot(
             snapshot_key: "default.system", 
             snapshot_location: $snapshots['default.system'], 
             migration_name: $migration->get_migration_name(),
             migration_timestamp: $migration->get_migration_timestamp(),
             up_operations: $migration->up()['default.system'] ?? []
         );

         //Run application migrations last
         $app_snapshots = array_diff_key($snapshots, array_flip(['default.system']));

         if(config('tenancy.enabled')){

             $command = CommandContext::init();

             $tenant_model = config('tenancy.model_class');

             $tenants = $tenant_model::using(system_connection())->get()->all();

             foreach($tenants as $t){

                 $command->attributes->set('tenant', $t);

                 foreach($app_snapshots as $s => $l){
                     $this->process_snapshot(
                         snapshot_key: $s, 
                         snapshot_location: $l, 
                         migration_name: $migration->get_migration_name(),
                         migration_timestamp: $migration->get_migration_timestamp(),
                         up_operations: $migration->up()[$s] ?? [],
                         tenant: $t
                     );
                 }
             }

         }else{

             foreach($app_snapshots as $s => $l){
                 $this->process_snapshot(
                     snapshot_key: $s, 
                     snapshot_location: $l, 
                     migration_name: $migration->get_migration_name(),
                     migration_timestamp: $migration->get_migration_timestamp(),
                     up_operations: $migration->up()[$s] ?? []
                 );
             }

         }

         return;
     }

     public function handle(CommandContext $context) : int {

         Cli::print("\n========================================");
         Cli::print("SaQle Database Migration");
         Cli::print("========================================\n");

         $files = array_values($this->sort_files(File::scandir(path: $this->migrations_folder, exts: ['php'])));

         foreach($files as $index => $f){
             $this->process_file($f, $index);
         } 

         return 0;
     } 
}
