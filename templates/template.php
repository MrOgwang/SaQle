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
 * The template class is used to add context values that are shared across views
 * 
 * This class is used together with the TemplateContextProvider.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+254741142038>
 * */
namespace SaQle\Templates;

class Template {
	 private static $instance;
	 private array $shared = [];

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

     public function set(string $name, mixed $value){
     	 $this->shared[$name] = $value;
     }

     public static function get_context() : array {
     	 $template = self::init();
     	 return $template->get_shared();
     }

     public function get_shared(){
     	 return $this->shared;
     }
}

