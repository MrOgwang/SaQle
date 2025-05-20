<?php
namespace SaQle\Orm\Database\Exceptions;

class ModelNotFoundException extends \Exception{
     protected $details;
     public function __construct($details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "There is no model or table called [{$this->details->model_name}] defined in the database context [{$this->details->db_context_name}]";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
