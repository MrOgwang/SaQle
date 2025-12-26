<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The class is used 
 * - to add context values that are shared across views
 * - to define named layouts
 * 
 * This class is used together with the TemplateContextProvider and the TemplateLayoutProvider.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+254741142038>
 * */
namespace SaQle\Templates;

class Template {
	 private static $instance;
	 private array $shared = [];
     private array $components = [];

	 private function __construct(){

	 }

     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public static function context(string $name, mixed $value){
     	 $template = self::init();
     	 $template->set($name, $value);
     }

     public static function layout(string $name, callable $value){
         $template = self::init();
         $template->set($name, $value, 'layout');
     }

     public function set(string $name, mixed $value, string $type = 'context'){
         if($type === 'context'){
             $this->shared[$name] = $value;
         }else{
             $this->components[$name] = $value;
         }
     }

     public static function get_context() : array {
     	 $template = self::init();
     	 return $template->get_shared();
     }

     public static function get_layout() : array {
         $template = self::init();
         return $template->get_components();
     }

     public function get_shared(){
     	 return $this->shared;
     }

     public function get_components(){
         return $this->components;
     }

     public static function has(string $key, string $type = 'context'){
         $template = self::init();
         if($type === 'context')
             return array_key_exists($key, $template->get_shared());

         return array_key_exists($key, $template->get_components());
     }
}

