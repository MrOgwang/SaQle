<?php
namespace SaQle\Dao\Operations\Crud\Exceptions;

class SelectOperationFailedException extends \Exception{
     protected $details;
     public function __construct($details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "Select operation command failed!";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
?>