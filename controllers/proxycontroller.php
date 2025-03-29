<?php
namespace SaQle\Controllers;

use SaQle\Controllers\Base\BaseController;
use ReflectionMethod;
use Exception;

abstract class ProxyController extends BaseController{
	 public protected(set) BaseController $controller {
	 	 set(BaseController $value){
	 	 	 $this->controller = $value;
	 	 }

	 	 get => $this->controller;
	 }

	 public function __construct(){
	 	 $this->controller = $this->destination();
	 }

	 abstract protected function destination() : BaseController;

	 public function __call($method, $args){
	 	 if(!method_exists($this->controller, $method)){
         	 throw new Exception('The method '.$method.' was not found on the controller! '.$this->controller::class);
         }

         $reflection_method = new ReflectionMethod($this->controller::class, $method);
         return $reflection_method->invokeArgs($this->controller, $args);
	 }

	 public function __get($name){
	 	 if(!property_exists($this->controller, $name)){
	 	 	 throw new Exception('The property '.$name.' was not found on the controller! '.$this->controller::class);
	 	 }

	 	 return $this->controller->$name;
     }

     public function __set($name, $value){
     	 if(!property_exists($this->controller, $name)){
	 	 	 throw new Exception('The property '.$name.' was not found on the controller! '.$this->controller::class);
	 	 }

     	 $this->controller->$name = $value;
     }

}
?>