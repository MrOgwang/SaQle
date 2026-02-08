<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Transaction;

use SaQle\Orm\Connection\{Connection, ConnectionManager};
use SaQle\Orm\Database\Config\ConnectionConfig;
use Throwable;

final class TransactionManager {
     private static array $transaction_level = [];

     public static function run(string $connection_name, callable $callback): mixed {
         
         $config = ConnectionConfig::from_connection($connection_name);
         $pdo = ConnectionManager::get($config, true);
         $transaction_key = spl_object_id($pdo);
         $connection_key = ConnectionManager::make_key($config, true);

         self::$transaction_level[$transaction_key] ??= 0;

         TransactionContext::push($connection_key, $pdo);

         try{
             if(self::$transaction_level[$transaction_key] === 0){
                 $pdo->beginTransaction();
             }else{
                 $pdo->exec('SAVEPOINT sp_' . self::$transaction_level[$transaction_key]);
             }

             self::$transaction_level[$transaction_key]++;

             $result = $callback();

             self::$transaction_level[$transaction_key]--;

             if(self::$transaction_level[$transaction_key] === 0){
                 $pdo->commit();
             }else{
                 $pdo->exec('RELEASE SAVEPOINT sp_' . self::$transaction_level[$transaction_key]);
             }

             return $result;

         }catch(Throwable $e){
             self::$transaction_level[$transaction_key]--;

             if($pdo->inTransaction()){
                 if(self::$transaction_level[$transaction_key] === 0){
                     $pdo->rollBack();
                 }else{
                     $pdo->exec('ROLLBACK TO SAVEPOINT sp_' . self::$transaction_level[$transaction_key]);
                 }
             }

             throw $e;
         }finally {
             TransactionContext::pop($connection_key);
         }
     }
}
