<?php
namespace SaQle\Core\Services\Providers;

class AppProvider{
     private static $instance;
     private static $providers = [];

     private function __construct(){
         
     }

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(array $items) : void {
         self::$providers = array_unique(array_merge(self::$providers, $items));
     }

     public static function load(){
         foreach(self::$providers as $p){
             new $p()->register();
         }
     }
}
