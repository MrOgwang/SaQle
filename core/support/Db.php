<?php
declare(strict_types = 1);

namespace SaQle\Core\Support;

use SaQle\Orm\Database\Drivers\{MySqlDriver, PostgreSqlDriver};
use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Database\Transaction\TransactionManager;
use ReflectionFunction;
use Exception;

class Db {

	 public function __construct(private string $connection_name){

	 }

     public static function driver(string $connection){
         $db_config = config('connections')[$connection];

         return match($db_config['driver']){
             'mysql' => new MySqlDriver(ConnectionConfig::from_connection($connection)),
             'pgsql' => new PostgreSqlDriver(ConnectionConfig::from_connection($connection))
         };
     }

	 public function transaction(string $connection_name, callable $callback){
         return TransactionManager::run($connection_name, $callback);
	 }
}
