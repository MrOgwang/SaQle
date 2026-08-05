<?php

declare(strict_types = 1);

namespace SaQle\Core\Support;

use SaQle\Orm\Database\Drivers\{
     DbDriver, 
     MySqlDriver, 
     PostgreSqlDriver
};
use SaQle\Orm\Connection\ConnectionConfig;
use SaQle\Orm\Database\Transaction\TransactionManager;
use SaQle\Orm\Database\DbProxy;
use SaQle\Orm\Database\Schema;
use SaQle\Orm\Database\SystemSchema;
use SaQle\Auth\Context\ActorContext;
use ReflectionFunction;
use Exception;

class Db {

     private string $connection_key;

     private function __construct(?string $connection_key = null){
         $this->connection_key = self::get_connection_key(connection_key: $connection_key);
     }

     public static function get_system_db() : array {
         return [config('framework_connection'), config('framework_database'), SystemSchema::class];
     }

     public static function register_tenant_db($connection_key, $tenant){

         $listed_connections = config('db.connections', []);
        
         if(!$listed_connections){
             return;
         }

         $connection_key_parts = explode(".", $connection_key);

         $connection_name = $connection_key_parts[0];
         $database_name = $connection_key_parts[1];

         $tenant_database_name = $database_name."_".strtolower(str_replace(" ", "_", $tenant->tenant_name));
         $tenant_database_schema = $listed_connections[$connection_name]['databases'][$database_name];

         $updated_databases = array_merge(
             $listed_connections[$connection_name]['databases'],
             [
                 $tenant_database_name => $tenant_database_schema
             ]
         );

         $listed_connections[$connection_name]['databases'] = $updated_databases;

         config()->set('db.connections', $listed_connections); 

         return [$connection_name.".".$tenant_database_name, $tenant_database_schema];
     }

     public static function register_system_db(){

         $listed_connections = config('db.connections', []);

         if(!$listed_connections){
             return;
         }

         $default_connection_name = config('db.default_connection', '');

         $default_connection = $listed_connections[$default_connection_name] ?? array_values($listed_connections)[0];
         
         $system_connection = $default_connection;

         $system_connection['databases'] = [
             config('framework_database') => SystemSchema::class
         ];

         $listed_connections[config('framework_connection')] = $system_connection;

         config()->set('db.connections', $listed_connections); 

         [config('framework_connection').".".config('framework_database'), SystemSchema::class];
     }

     /**
      * This method is defective: It assumes that a model will be tied to only
      * one database schema. In practice, especially the way the db.config file is setup,
      * the same model may find itself in several database schemas.
      * */
     public static function get_connection_schema(
         ?string $model_class = null,
         ?string $connection_key = null
     ){
         $connection_key = ActorContext::is_platform() ? 
         system_connection() :
         self::get_connection_key($model_class, $connection_key);

         $key_parts = explode(".", $connection_key);

         $config_key = "db.connections.".$key_parts[0].".databases.".$key_parts[1];

         $schema = trim(config($config_key, ''));

         if(!$schema || !class_exists($schema) || !is_subclass_of($schema, Schema::class)){
             throw new Exception('The connection or schema does not exist!');
         }

         return [$connection_key, $schema];
     }

     public static function get_connection_key(
         ?string $model_class = null, 
         ?string $connection_key = null
     ){

         $connection = trim(config('db.default_connection', ''));
         $database = trim(config('db.default_database', ''));

         if(!$connection || !$database){
             throw new Exception("Please provide both default_connection and default_database settings in db config!");
         }

         if(is_null($connection_key) || trim($connection_key) === ""){

             if(!$model_class){
                 $connection_key = $connection.".".$database;
             }else{
                 /**
                  * if the $connection_key is not provided and the $model_class is, 
                  * we will search through the list of connections
                  * until we find the first one where the model is registered
                  * */
                 $connections = config('db.connections');

                 foreach($connections as $n => $props){
                     foreach($props['databases'] as $d => $schema){
                         $index = (new $schema())->model_exists($model_class);

                         if($index !== false){
                             $connection_key = $n.".".$d;
                             break 2;
                         }
                     }
                 }

                 if(!$connection_key){
                     throw new Exception("The model: {$model_class} is not registsred with any schemas!");
                 }

             }
         }else{

             $key_parts = explode(".", $connection_key);

             if(count($key_parts) > 2){
                 throw new Exception("Please provide a valid connection key in the format [connection_name.database_name]");
             }

             if(count($key_parts) === 1){
                 $connection_key = $key_parts[0].".".$database;
             }
         }

         return $connection_key;
     }

     //use default connection in transaction
     public static function transaction(callable $callback) : mixed {
         return (new self())->run_transaction($callback);
     }

     //use default connection for driver
     public static function driver(bool $with_database = true) : DbDriver {
         return (new self())->resolve_driver($with_database);
     }

     //switch connection
     public static function using(string $connection_key) : DbProxy {
         return new DbProxy(new self($connection_key));
     }

     public function run_transaction(callable $callback) : mixed {
         return TransactionManager::run($this->connection_key, $callback);
     }

     public function resolve_driver(bool $with_database = true) : DbDriver {

         $config = ConnectionConfig::from_connection($this->connection_key, $with_database);
        
         return match($config->get_driver()){
             'mysql' => new MySqlDriver($config),
             'pgsql' => new PostgreSqlDriver($config)
         };
     }

     public static function get_developer_schemas() : array {

         $schemas = [];

         foreach(config('db.connections') as $connection_name => $connection_config){
             foreach($connection_config['databases'] as $db_name => $db_schema){
                 if(!is_a($db_schema, SystemSchema::class, true)){
                     $schemas[$db_name] = $db_schema;
                 }
             }
         }

         return $schemas;
     }
}
