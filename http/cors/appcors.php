<?php
namespace SaQle\Http\Cors;

class AppCors{
     private static $instance;
     private static $allowed_origins   = ['*'];
     private static $allowed_methods   = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
     private static $allowed_headers   = ['*'];
     private static $required_headers  = ['Origin', 'Host', 'Referer', 'Accept', 'Content-Type'];
     private static $allow_credentials = true;

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function origins(array $origins){
         self::$allowed_origins = $origins;
     }

     public static function getorigins(){
         return self::$allowed_origins;
     }

     public static function headers(array $headers){
         self::$allowed_headers = $headers;
     }

     public static function requiredheaders(array $headers){
         self::$required_headers = array_unique(array_merge($headers, self::$required_headers));
     }

     public static function getheaders(){
         return self::$allowed_headers;
     }

     public static function getrequiredheaders(array $headers){
         return self::$required_headers;
     }

     public static function methods(array $methods){
         self::$allowed_methods = $methods;
     }

     public static function getmethods(){
         return self::$allowed_methods;
     }

     public static function credentials(bool $cred){
         self::$allow_credentials = $cred;
     }

     public static function getcredentials(){
         return self::$allow_credentials;
     }
}
?>