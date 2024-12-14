<?php
namespace SaQle\Templates\Context;

class AppContext{
     private static $instance;
	 private static $context = [];

	 private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(array $context) : void{
         self::$context = array_merge(self::$context, $context);
     }

     public static function get_context() : array{
         return self::$context;
     }
}
?>