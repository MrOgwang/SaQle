<?php
namespace SaQle\Config;

class AppConfig{
     private static $instance;
     private static $directory = "";

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function load(){
         $configurations = require_once self::$directory.'/config.php';
         $config = new Config(...$configurations);
     }

     public static function directory(string $dir){
         self::$directory = $dir;
         self::load();
     }

     public static function getdirectory(){
         return self::$directory;
     }
}
?>