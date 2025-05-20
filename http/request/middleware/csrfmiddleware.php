<?php
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;

class CsrfMiddleware extends IMiddleware{
     private static string $token_key      = 'csrf_token';
     private static array  $except_methods = ['GET', 'HEAD', 'OPTIONS'];
     public function handle(MiddlewareRequestInterface &$request){
         if(session_status() == PHP_SESSION_NONE){
             session_start();
         }

         
         //Generate CSRF token if not set
         $token_key      = CsrfMiddleware::get_token_key();
         $except_methods = CsrfMiddleware::get_except_methods();
         $token          = $request->context->get($token_key);
         if(!$token){
             $request->context->set($token_key, bin2hex(random_bytes(32)), true);
         }

         //skip CSRF check for safe HTTP methods
         if(in_array($_SERVER['REQUEST_METHOD'], $except_methods)){
             parent::handle($request);
             return;
         }

         //validate CSRF token for state-changing requests
         $submitted_token = $request->data->get($token_key, $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

         if(!$submitted_token || $submitted_token !== $token){
             unauthorized_exception('CSRF token validation failed');
         }
         
     	 parent::handle($request);
     }

     public static function get_token_key() : string {
         return self::$token_key;
     }

     public static function get_except_methods() : array {
         return self::$except_methods;
     }

     public static function get_token(): string {
         $request = resolve('request');
         $token_key = CsrfMiddleware::get_token_key();
         if(session_status() == PHP_SESSION_NONE){
             session_start();
         }
         return $request->context->get($token_key, '');
     }
}
