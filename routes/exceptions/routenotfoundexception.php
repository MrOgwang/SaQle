<?php
namespace SaQle\Routes\Exceptions;

class RouteNotFoundException extends \Exception{
     protected $details;
     public function __construct(...$details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "The resource [".$this->details['url']."] either does not exist or has been permanently moved!";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
