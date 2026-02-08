<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Transaction;

use PDO;

final class TransactionContext {
     /**
     * @var array<string, list<PDO>>
     * connection_key => stack of PDOs
     */
     private static array $stack = [];

     public static function push(string $connection_key, PDO $pdo): void {
         self::$stack[$connection_key][] = [
            'pdo'      => $pdo,
            'envelope' => new TransactionEnvelope()
         ];
     }

     public static function pop(string $connection_key): ?TransactionEnvelope {
         if (empty(self::$stack[$connection_key])) {
            return null;
         }

         $entry = array_pop(self::$stack[$connection_key]);

         if(empty(self::$stack[$connection_key])) {
             unset(self::$stack[$connection_key]);
         }

         return $entry['envelope'];
     }

     public static function current(string $connection_key): ?PDO {
         if(!isset(self::$stack[$connection_key])){
             return null;
         }

         return end(self::$stack[$connection_key]) ?: null;
     }

     public static function has(string $connection_key): bool {
         return isset(self::$stack[$connection_key]);
     }

     public static function active(string $connection_key): bool {
        return !empty(self::$stack[$connection_key]);
     }

     public static function envelope(string $connection_key): TransactionEnvelope {
        return self::$stack[$connection_key][array_key_last(self::$stack[$connection_key])]['envelope'];
     }
}
