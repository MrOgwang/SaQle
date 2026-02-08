<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Transaction;

use PDO;

final class TransactionContext {
     /**
     * @var array<string, list<PDO>>
     * connection_name => stack of PDOs
     */
     private static array $stack = [];

     public static function push(string $connection_name, PDO $pdo): void {
         self::$stack[$connection_name][] = $pdo;
     }

     public static function pop(string $connection_name): void {
         array_pop(self::$stack[$connection_name]);

         if(empty(self::$stack[$connection_name])) {
             unset(self::$stack[$connection_name]);
         }
     }

     public static function current(string $connection_name): ?PDO {
         if(!isset(self::$stack[$connection_name])) {
             return null;
         }

         return end(self::$stack[$connection_name]) ?: null;
     }

     public static function has(string $connection_name): bool {
         return isset(self::$stack[$connection_name]);
     }
}
