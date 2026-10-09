<?php

namespace SaQle\Core\Registries;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Support\Index;
use SaQle\Http\Attributes\{
     Get,
     Post,
     Patch,
     Put,
     Delete
};
use SaQle\Core\Components\Component;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

final class ComponentRegistry {

     private static ?array $components = null;

     public static function all(bool $reload = false): array {

         $cache_path = path_join([config('base_path'), config('class_mappings_dir'), 'Components.php']);

         if(!file_exists($cache_path)){
             return [];
         }

         self::$components = require $cache_path;

         return self::$components;
     }

     private static function get_actual_name(string $name){

         $parts = explode("@", $name);
         
         return $parts[0];
     }

     public static function exists(string $name): bool {

         $name = self::get_actual_name($name);
         
         return array_key_exists($name, self::all());
     }

     public static function assert_exists(string $name){
         if(!self::exists($name)){
             throw new InvalidArgumentException("The component: '{$name}' does not exist!");
         }
     } 

     public static function get(string $name, bool $convert = false): null|array|Component {
         
         $components = self::all();

         $name = self::get_actual_name($name);

         $c = $components[$name] ?? null;

         if(!is_null($c) && $convert){
             return Component::from_array($c);
         }

         return $c;
     }

     public static function get_or_fail(string $name, bool $convert = false): array|Component {

         self::assert_exists($name);
         
         return self::get($name, $convert);
     }
}
