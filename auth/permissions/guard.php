<?php
namespace SaQle\Auth\Permissions;

use Closure;

class Guard {
     static protected array $rules = [];

     static public function define(string $name, Closure $rule){
         self::$rules[$name] = $rule;
     }

     static public function check(string $name, array $params = []){
         $callback = self::$rules[$name] ?? null;

         if(!$callback){
             return false;
         }

         array_unshift($params, resolve('request')->user);
         
         return $callback(...$params);
     }
}
?>
