<?php
namespace SaQle\Core\Services\Container;

class AppContainer{
     private static Container $container;
     private static $instance;
     private static $providers = [];

     private function __construct(){
         self::$container = Container::init();
     }

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(array $items) : void{
         self::$providers = array_unique(array_merge(self::$providers, $items));
     }

     public static function load(){
         foreach(self::$providers as $provider){
             (new $provider())->register(self::$container);
         }
     }
}
?>