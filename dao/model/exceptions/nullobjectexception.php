<?php
namespace SaQle\Dao\Model\Exceptions;

class NullObjectException extends \Exception{
     protected $details;
     public function __construct(...$details){
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