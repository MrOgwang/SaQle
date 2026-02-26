<?php
declare(strict_types = 1);

namespace SaQle\Core\Support;

use SaQle\Orm\Database\Drivers\{DbDriver, MySqlDriver, PostgreSqlDriver};
use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Database\Transaction\TransactionManager;
use SaQle\Orm\Database\DbProxy;
use SaQle\Build\Utils\MigrationUtils;
use ReflectionFunction;
use Exception;

class Db {

     private string $connection_name;

     private function __construct(?string $connection_name = null){
         $this->connection_name = ($connection_name ?? config('db.default_connection')) ?? 
         array_keys(config('db.connections'), [])[0] ?? '';

         if(!$this->connection_name || !MigrationUtils::is_schema_defined($this->connection_name)){
             throw new Exception("Please provide a valid database connection name!");
         }
     }

     //use default connection in transaction
     public static function transaction(callable $callback) : mixed {
         return (new self())->run_transaction($callback);
     }

     //use default connection for driver
     public static function driver() : DbDriver {
         return (new self())->resolve_driver();
     }

     //switch connection
     public static function using(string $connection_name) : DbProxy {
         return new DbProxy(new self($connection_name));
     }

     public function run_transaction(callable $callback) : mixed {
         return TransactionManager::run($this->connection_name, $callback);
     }

     public function resolve_driver() : DbDriver {
         $db_config = config('db.connections')[$this->connection_name];

         return match($db_config['driver']){
             'mysql' => new MySqlDriver(ConnectionConfig::from_connection($this->connection_name)),
             'pgsql' => new PostgreSqlDriver(ConnectionConfig::from_connection($this->connection_name))
         };
     }
}
