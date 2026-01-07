<?php

namespace SaQle\Auth\Guards;

use Closure;
use SaQle\Auth\Guards\Exceptions\UnauthorizedException;
use SaQle\Core\FeedBack\FeedBack;

final class Guard {
     protected static array $guards = [];

     public static function add(string $name, Closure $rule): void {
         self::$guards[$name] = $rule;
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
             self::$guards[$name] ?? null,
             $user,
             $args
         );
     }

     public static function authorize(string $name, $user = null, ...$args): true {
         if (!self::check($name, $user, ...$args)) {
             throw new UnauthorizedException(
                 code: FeedBack::UNAUTHORIZED
             );
         }

         return true;
     }
}
