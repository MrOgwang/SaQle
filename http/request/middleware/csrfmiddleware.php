<?php
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Response\HttpMessage;

class CsrfMiddleware implements MiddlewareInterface {

     private static string $token_key = 'csrf_token';
     
     public function handle($request, $response = null) : ?HttpMessage {

         //Generate CSRF token if not set
         $token_key = CsrfMiddleware::get_token_key();
         $token = $request->session->get($token_key);
         if(!$token){
             $request->session->set($token_key, bin2hex(random_bytes(32)), true);
         }

         //skip CSRF check for safe HTTP methods
         if($request->is_safe()){
             return null;
         }

         //validate CSRF token for state-changing requests
         $submitted_token = $request->data->get($token_key, $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

         if(!$submitted_token || $submitted_token !== $token){
             authorization_exception('CSRF token validation failed')->throw();
         }

         return null;
     }

     public static function get_token_key() : string {
         return self::$token_key;
     }

     public static function get_token(): string {
         $request = resolve('request');
         $token_key = CsrfMiddleware::get_token_key();
         if(session_status() === PHP_SESSION_NONE){
             session_start();
         }
         return $request->session->get($token_key, '');
     }
}
