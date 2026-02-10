<?php

namespace SaQle\Orm\Connection;

use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Database\Transaction\TransactionContext;

final class ConnectionManager {
     /** @var array<string, Connection> */
     protected static array $connections = [];

     public static function get(ConnectionConfig $config, bool $with_database = true) {
         $key = self::make_key($config, $with_database);

         //1. is there an active transaction for this connection?
         $pdo = TransactionContext::current($key)['pdo'] ?? null;

         if($pdo){
             return [$pdo, $key];
         }

         //2. Resolve connection normally
         if(!isset(self::$connections[$key])){
             $params = $config->to_array();

             if(!$with_database){
                 $params['database'] = '';
             }

             self::$connections[$key] = resolve(Connection::class, $params);
         }

         return [self::$connections[$key], $key];
     }

     private static function make_key(ConnectionConfig $config, bool $with_database): string {
         return implode(':', [
             $config->get_driver(),
             $config->get_host(),
             $config->get_port(),
             $config->get_database(),
             $with_database ? 'db' : 'no-db'
         ]);
    }
}
