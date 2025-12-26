<?php

namespace SaQle\Core\Registries;

use InvalidArgumentException;

final class ComponentRegistry {
     private static ?array $components = null;

     public static function all(): array{
         if (self::$components === null) {
             self::$components = require DOCUMENT_ROOT.CLASS_MAPPINGS_DIR.'components.php';
         }

         return self::$components;
     }

     public static function exists(string $name): bool {
         return array_key_exists($name, self::all());
     }

     public static function get(string $name): array {
         $components = self::all();

         if (!isset($components[$name])){
            throw new InvalidArgumentException("Component [$name] does not exist.");
         }

         return $components[$name];
     }
}
