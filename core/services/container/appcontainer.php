<?php
namespace SaQle\Core\Services\Container;

class AppContainer{
     private static Container $container;
     private static $instance;
     private static $locators = [];

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
         self::$locators = array_unique(array_merge(self::$locators, $items));
     }

     public static function load(){
         foreach(self::$locators as $loc){
             (new $loc())->register(self::$container);
         }
     }
}
?>