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
         [$pdo, $connection_key] = ConnectionManager::get($config, true);
         $transaction_key = spl_object_id($pdo);

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

                 //RELEASE EVENTS ONLY AT OUTERMOST COMMIT
                 $envelope = TransactionContext::pop($connection_key);
                 $envelope?->commit();
             }else{
                 $pdo->exec('RELEASE SAVEPOINT sp_' . self::$transaction_level[$transaction_key]);

                 //nested commit → merge envelopes upward
                 $child = TransactionContext::pop($connection_key);
                 $parent = TransactionContext::envelope($connection_key);

                 foreach($child->events ?? [] as $event){
                     $parent->record($event);
                 }
             }

             return $result;

         }catch(Throwable $e){
             self::$transaction_level[$transaction_key]--;

             if($pdo->inTransaction()){
                 if(self::$transaction_level[$transaction_key] === 0){
                     $pdo->rollBack();

                     TransactionContext::pop($connection_key)?->rollback();
                 }else{
                     $pdo->exec('ROLLBACK TO SAVEPOINT sp_' . self::$transaction_level[$transaction_key]);

                     TransactionContext::pop($connection_key)?->rollback();
                 }
             }

             throw $e;
         }finally{
             TransactionContext::pop($connection_key);
         }
     }
}
