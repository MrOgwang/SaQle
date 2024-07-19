<?php
namespace SaQle\Dao\Operations\Crud\Exceptions;

class DeleteOperationFailedException extends \Exception{
     protected $details;
     public function __construct($details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "Delete operation command failed!";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
?>