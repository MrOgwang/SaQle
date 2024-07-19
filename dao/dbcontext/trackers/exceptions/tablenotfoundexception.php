<?php
namespace SaQle\Dao\Trackers\Exceptions;

class TableNotFoundException extends \Exception{
     protected $details;
     public function __construct($details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "There is no table called [{$this->details->table_name}] in the context tracker: Available tables are: ".implode(", ", $this->details->tables);
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
?>