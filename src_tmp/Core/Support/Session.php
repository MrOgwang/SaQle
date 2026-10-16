<?php

namespace SaQle\Core\Support;

use SaQle\Http\Request\Request;
use SaQle\Session\Session as RealSession;
use RuntimeException;

final class Session {  

     private static function real(): RealSession {
         
         $request = Request::get();

         if(!$request){
             throw new RuntimeException('Session accessed before Request initialization.');
         }

         return $request->session();
     }

     public static function start(Request $request){
         self::real()->start($request);
     }

     public static function close(Request $request){
         self::real()->close($request);
     }

     public static function flash(string $key, mixed $value){
         self::real()->flash($key, $value);
     }

     public static function get_flash(string $key, mixed $default = null){
         self::real()->get_flash($key, $default);
     }

     public static function set(string $key, mixed $value): void {
         self::real()->set($key, $value);
     }

     public static function get(string $key, mixed $default = null): mixed {
         return self::real()->get($key, $default);
     }

     public static function get_or_fail(string $key): mixed {
         return self::real()->get_or_fail($key);
     }

     public static function get_all() : array {
         return self::real()->get_all();
     }

     public static function exists(string $key): bool {
         return self::real()->exists($key);
     }

     public static function has(string $key): bool {
         return self::real()->exists($key);
     }

     public static function remove(string $key): bool {
         return self::real()->remove($key);
     }
}
