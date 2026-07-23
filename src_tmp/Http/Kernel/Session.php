<?php

namespace SaQle\Http\Kernel;

use SaQle\Http\Request\Request;
use SaQle\Http\Request\Data\Session as RequestSession;
use RuntimeException;

final class Session {  

     public static function start(Request $request){
         if($request->is_web_request()){

             if(session_status() === PHP_SESSION_NONE){
                 session_start();
             }

             $request->session->activate_session();

             self::promote_flash_data($request);
         }
     }

     public static function close(Request $request){
         if($request->is_web_request()){
             $request->session()->remove('__flash_current');
         }
     }

     private static function promote_flash_data(Request $request){
         if($request->session()->exists('__flash_next')){
             $request->session()->set('__flash_current', $request->session()->get('__flash_next'));
             $request->session()->remove('__flash_next');
         }
     }

     private static function real(): RequestSession {
         $request = Request::get();

         if(!$request){
             throw new RuntimeException('Session accessed before Request initialization.');
         }

         return $request->session();
     }

     public static function flash(string $key, mixed $value){

         $flash = self::get('__flash_next', []);

         $flash[$key] = $value;

         self::set("__flash_next", $flash, true);
     }

     public static function get_flash(string $key, mixed $default = null){
         return self::get('__flash_current', [])[$key] ?? $default;
     }

     public static function set(string $key, mixed $value, bool $persistent = true): void {
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
         return self::real()->remove($key);
     }
}
