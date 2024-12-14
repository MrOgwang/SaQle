<?php
namespace SaQle\Templates\Static;

class AppStatic{
     private static $instance;
	 private static $csslinks    = [];
     private static $jslinks     = [];
     private static $javascripts = [];
     private static $typescripts = [];

	 private function __construct(){}

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function link(string $type, array $links = []) : void{
         if($type === 'css'){
             self::$csslinks = array_merge(self::$csslinks, $links);
         }elseif($type === 'js'){
             self::$jslinks = array_merge(self::$jslinks, $links);
         }
     }

     public static function script(string $script, string $type = 'javascript') : void{
         if($type === 'typescript'){
             self::$typescripts[] = $script;
         }elseif($type === 'javascript'){
             self::$javascripts[] = $script;
         }
     }

     public static function get_css_links(){
         return self::$csslinks;
     }

     public static function get_js_links(){
         return self::$jslinks;
     }

     public static function get_javascripts(){
         return self::$javascripts;
     }

     public static function get_typescripts(){
         return self::$typescripts;
     }
}
?>