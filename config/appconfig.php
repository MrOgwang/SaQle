<?php
namespace SaQle\Config;

class AppConfig{
     private static $instance;
	 private static $configurations = [];
     private static $directory      = "";

	 private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function register(array $configurations) : void{
         self::$configurations = $configurations;
     }

     public static function load(){
         $config = new Config(...self::$configurations);
     }

     public static function directory(string $dir){
         self::$directory = $dir;
     }
}
?>