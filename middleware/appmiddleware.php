<?php
namespace SaQle\Middleware;

class AppMiddleware{
     private static $instance;
	 private static $middlewares = [];

	 private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(array $middlewares) : void{
         self::$middlewares = array_merge(self::$middlewares, $middlewares);
     }

     public static function get(){
         return self::$middlewares;
     }
}
?>