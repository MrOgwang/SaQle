<?php
namespace SaQle\Controllers\Refs;

class ControllerRef{
     private static $instance;
	 private static $controllers = [];

	 private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(array $controllers) : void{
         self::$controllers = array_merge(self::$controllers, $controllers);
     }

     public static function add(string $name, string $class) : void{
         self::$controllers[$name] = $class;
     }

     public static function get_controllers() : array{
         return self::$controllers;
     }
}
?>