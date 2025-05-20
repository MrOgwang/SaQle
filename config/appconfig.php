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
         $files = ['app', 'auth', 'database', 'email', 'model', 'tenant'];
         $configurations = [];
         foreach($files as $f){
             $path = self::$directory.'/'.$f.'.config.php';
             if(file_exists($path)){
                 $configurations = array_merge($configurations, require_once $path);
             }
         }
         
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
