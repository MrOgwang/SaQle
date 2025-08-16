<?php
namespace SaQle\Auth\Permissions;

use Closure;
use SaQle\Auth\Permissions\Exceptions\{AccessDeniedException, UnauthorizedAccessException};
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Auth\Models\Interfaces\IUser;

class Guard {
     static protected array $roles       = [];
     static protected array $attributes  = [];
     static protected array $permissions = [];

     static public function role(string $name, Closure $rule){
         self::$roles[$name] = $rule;
     }

     static public function permission(string $name, Closure $rule){
         self::$permissions[$name] = $rule;
     }

     static public function attribute(string $name, Closure $rule){
         self::$attributes[$name] = $rule;
     }

     private static function evaluate($callback, $user, ...$args){
         if(!$callback || !is_callable($callback)){
             return false;
         } 
         
         return $callback($user ?? resolve('request')->user, ...$args);
     }

     /**
      * This does a role check
      * 
      * @param string $name: the name of the role
      * @param IUser  $user: the user to perform this check against
      * @param array  $args: extra arguments to pass
      * */
     static public function check(string $name, ?IUser $user = null, ...$args){
         $callback = self::$roles[$name] ?? null;
         return self::evaluate($callback, $user, ...$args);
     }

     /**
      * Allow does a permissions check.
      * 
      * @param string $name: the name of the permission
      * @param IUser  $user: the user to perform this check against
      * @param array  $args: extra arguments to pass
      * 
      * Allow returns true or false and doesn't throw an error
      * */
     static public function allow(string $name, ?IUser $user = null, ...$args){
         $callback = self::$permissions[$name] ?? null;
         return self::evaluate($callback, $user, ...$args);
     }

     /**
      * Authorize does a permissions check
      * 
      * @param string $name: the name of the permission
      * @param IUser  $user: the user to perform this check against
      * @param array  $args: extra arguments to pass
      * 
      * Authorize throws an error if the check fails
      * */
     static public function authorize(string $name, ?IUser $user = null, ...$args){
         $passed = self::allow($name, $user, ...$args);
         if(!$passed)
             throw new UnauthorizedAccessException(code: FeedBack::UNAUTHORIZED);

         return true;
     }

     /**
      * is does an attribute check
      * 
      * @param string $name: the name of the attribute
      * @param IUser  $user: the user to perform this check against
      * @param array  $args: extra arguments to pass
      * 
      * */
     static public function is(string $name, ?IUser $user = null, ...$args){
         $callback = self::$attributes[$name] ?? null;
         return self::evaluate($callback, $user, ...$args);
     }
}

