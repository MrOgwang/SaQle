<?php
namespace SaQle\Orm\Entities\Model\Exceptions;

class NullObjectException extends \Exception{
     protected $details;
     public function __construct(...$details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "No object was found from table ".$this->details['table']." that matches your selection creteria";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
?>