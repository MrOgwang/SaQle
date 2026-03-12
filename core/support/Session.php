<?php

namespace SaQle\Core\Support;

use SaQle\Http\Request\Request;
use RuntimeException;

final class Session {

     private static function real(): \SaQle\Http\Request\Data\Session {
         $request = Request::get();

         if(!$request){
             throw new RuntimeException('Session accessed before Request initialization.');
         }

         return $request->session();
     }

     public static function set(string $key, mixed $value, bool $persistent = false): void {
         self::real()->set($key, $value, $persistent);
     }

     public static function get(string $key, mixed $default = null): mixed {
         return self::real()->get($key, $default);
     }

     public static function get_or_fail(string $key): mixed {
         return self::real()->get_or_fail($key);
     }

     public static function exists(string $key): bool {
         return self::real()->exists($key);
     }

     public static function has(string $key): bool {
         return self::real()->exists($key);
     }

     public static function remove(string $key): bool {
         self::real()->remove($key);
     }
}
