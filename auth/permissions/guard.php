<?php
namespace SaQle\Auth\Permissions;

use Closure;
use SaQle\Auth\Permissions\Exceptions\{AccessDeniedException, UnauthorizedAccessException};
use SaQle\Core\FeedBack\FeedBack;

class Guard {
     static protected array $rules = [];

     static public function role(string $name, Closure $rule){
         self::$rules[$name] = $rule;
     }

     static public function permission(string $name, Closure $rule){
         self::$rules[$name] = $rule;
     }

     /**
      * This does a role check. Role checks only take the loggedin user as an
      * argument
      * */
     static public function check(string $name){
         $callback = self::$rules[$name] ?? null;

         if(!$callback){
             return false;
         }
         
         return $callback(resolve('request')->user);
     }

     /**
      * Allow does a permissions check. A permission check may take in other arguments, especially
      * the resource(Either a model or a controller)
      * 
      * Allow returns true or false and doesn't throw an error
      * */
     static public function allow(string $name, ...$params){
         $callback = self::$rules[$name] ?? null;

         if(!$callback){
             return false;
         }

         array_unshift($params, resolve('request')->user);
         
         return $callback(...$params);
     }

     /**
      * Authorize does a permissions check. A permission check may take in other arguments, especially
      * the resource(Either a model or a controller)
      * 
      * Authorize throws an error if the check fails
      * */
     static public function authorize(string $name, ...$params){
         $passed = self::allow($name, ...$params);
         if(!$passed)
             throw new UnauthorizedAccessException(code: FeedBack::UNAUTHORIZED);

         return true;
     }
}

