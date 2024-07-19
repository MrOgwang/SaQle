<?php
namespace SaQle\Http\Request\Data\Exceptions;

class KeyNotFoundException extends \Exception{
     protected $name;
     public function __construct($name){
         $this->name = $name;
         parent::__construct();
     }
     public function __toString(){
		  return "Data item key [{$this->name}] does not exist in the current request context";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
?>