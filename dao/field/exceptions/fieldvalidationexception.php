<?php
namespace SaQle\Dao\Field\Exceptions;
class FieldValidationException extends \Exception{
     protected $details;
     public function __construct($details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		 $message = "One or more fields failed field validation as follows: \n";
		 $field_index = 0;
		 foreach($this->details as $field => $error){
			 $field_index += 1;
			 $message .= "\n".$field." : ".$error."\n";
		 }
         return $message;
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
?>