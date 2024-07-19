<?php
namespace SaQle\Dao\Trackers\Exceptions;

class DatabaseNotFoundException extends \Exception{
     protected $details;
     public function __construct($details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "There is no database called [{$this->details->database_name}] in the context tracker: Available databases are: ".implode(", ", $this->details->databases);
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
?>