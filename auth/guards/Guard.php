<?php

namespace SaQle\Auth\Guards;

use Closure;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Auth\Exceptions\AuthorizationException;

final class Guard {

     protected static array $guards = [];

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

         $resolved_user = $user ?? resolve('request')->user;

         return (bool) $callback($resolved_user, ...$args);
     }

     public static function check(string $name, $user = null, ...$args): bool {
         return self::evaluate(
             self::$guards[$name]['evaluate'] ?? null,
             $user,
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
             self::fail($name);
         }

         return true;
     }
}
