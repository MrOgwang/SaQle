<?php
namespace SaQle;

use Closure;

class Autoloader{
     private static $instance;
     
     private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(?Closure $loader = null){
        spl_autoload_register($loader ?? [__CLASS__, 'load']);
     }

     public static function load($class){

         if(stripos($class, 'SaQle') !== false){
             $saqle = dirname($_SERVER['DOCUMENT_ROOT']).'/saqle/';
             $class = strtolower(str_replace("\\", "/", str_replace("saqle", "", strtolower($class))));
             $file  = $saqle.$class.".php";

             if(file_exists($file)){
                require $file;
             }
         }

     }
}
