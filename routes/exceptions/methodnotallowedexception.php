<?php
namespace SaQle\Routes\Exceptions;

class MethodNotAllowedException extends \Exception{
     protected $details;
     public function __construct(...$details){
         $this->details = $details;
         parent::__construct();
     }
     public function __toString(){
		  return "The request method [".$this->details['method']."] is not allowed for the resource [".$this->details['url']."]. 
          Valid methods are [".implode(',', $this->details['methods'])."]!";
     }
	 public function get_message(){
		 return $this->__toString();
	 }
}
