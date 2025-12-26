<?php
namespace SaQle\Controllers\Base;

use SaQle\Http\Response\HttpMessage;
use Exception;

abstract class BaseController{
	 protected $request;
	 
	 public function __construct(){
	 	 $this->request = resolve('request');
	 }

	 /**
     * This method is called before controller method execution.
     * Override in child controllers to modify request input as needed.
     */
     public function on_method_start(array $input, string $method): array {
         return $input;
     }
}
