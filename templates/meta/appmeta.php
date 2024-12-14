<?php
namespace SaQle\Templates\Meta;

class AppMeta{
     private static $instance;
	 private static $metatags = [];

	 private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function tags(array $tags) : void{
         self::$metatags = array_merge(self::$metatags, $tags);
     }

     public static function get_meta_tags(){
         return self::$metatags;
     }
}
?>