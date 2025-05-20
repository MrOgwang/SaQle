<?php
namespace SaQle\Orm\Operations\Crud\Exceptions;

class UpdateOperationFailedException extends \Exception{
     protected $details;
     public function __construct($details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "Update operation command failed!";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
