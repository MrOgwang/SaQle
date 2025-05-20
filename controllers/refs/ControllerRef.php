<?php
namespace SaQle\Controllers\Refs;

class ControllerRef{
     private static $instance;
	 private static $controllers = [];
     private static $views       = [];
     private static $targets     = [];

	 private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(array $items, string $type = 'controllers') : void{
         if($type === 'controllers'){
             self::$controllers = array_merge(self::$controllers, $items);
         }else{
             self::$views = array_merge(self::$views, $items);
         }
     }

     public static function get_controllers() : array{
         return self::$controllers;
     }

     public static function get_views() : array{
         return self::$views;
     }

     public static function get_targets() : array{
         return self::$targets;
     }
}
