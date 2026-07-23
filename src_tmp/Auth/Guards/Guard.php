<?php

namespace SaQle\Auth\Guards;

use Closure;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Auth\Exceptions\AuthorizationException;

final class Guard {

     protected static array $guards = [];

     protected static array $before = [];

     public static function before(Closure $callback): void {
         self::$before[] = $callback;
     }

     protected static function run_before(string $guard, $user, array $args): ?bool {
         foreach(self::$before as $callback){
             $result = $callback($user, $guard, ...$args);

             if($result !== null){
                 return (bool)$result;
             }
         }

         return null;
     }

     public static function add(string $name, Closure $evaluate, ?Closure $fail = null): void {
         self::$guards[$name] = [
             'evaluate' => $evaluate,
             'fail'     => $fail
         ];
     }

     protected static function evaluate(?Closure $callback, $user, array $args): bool {
         if (!$callback) {
             return false;
         }

         return (bool) $callback($user, ...$args);
     }

     public static function check(string $name, $user = null, ...$args): bool {

         $resolved_user = $user ?? resolve('request')->user;

         $before = self::run_before($name, $resolved_user, $args);

         if($before !== null){
             return $before;
         }

         return self::evaluate(
             self::$guards[$name]['evaluate'] ?? null,
             $resolved_user,
             $args
         );
     }

     public static function fail(string $name) : mixed {
        
         $fail_callback = self::$guards[$name]['fail'] ?? null;

         if($fail_callback){
             $fail_callback(request());
         }

         throw new AuthorizationException('Unauthorized!');
     }

     public static function authorize(string $name, $user = null, ...$args): true {
         if(!self::check($name, $user, ...$args)){
             return self::fail($name);
         }

         return true;
     }
}
