<?php
namespace SaQle\Http\Request\Processors;

use SaQle\Services\Container\Cf;
use SaQle\Http\Response\HttpMessage;

class RequestProcessor{
	 protected $request;

	 public function __construct(){
	 	 $this->request = Cf::create('request');
	 }
     
     /**
      * Get the method to be called from a target controller.
      * This is the method that will return an http response object
      * 
      * How do we determine the method to call?
      * 
      * 1. When defining routes, the target of a url may be explicitly defined in this format: ControllerClassName@targetMethod
      * 2. Where target method is not explicitly named in the route, the request method(GET, POST, PUT, PATCH) will be used to determine the target method.
      *    It is assumed that the controller class defines a method get, post, put or patch according to the request method, otherwise throw an exception.
      * 
      * @param string $target: The name of the controller class
      * @return string $classname, string $method
      * */
	 protected function get_target_method(string $target) : array{
	 	 $target_parts     = explode("@", $target);
	 	 $target_classname = $target_parts[0];
	 	 $target_method    = $target_parts[1] ?? strtolower($this->request->route->get_method());

         if(!method_exists($target_classname, $target_method)){
         	 throw new \Exception('The target action '.$target_method.' was not found on the target controller!');
         }

         return [$target_classname, $target_method];
	 }

	 /**
	  * Call the controllers target method to return a key => value array response.
	  * 
	  * @param string $target
	  * @return array $response_data;
	  * */
	 public function get_target_response(string $target_classname, string $target_method) : HttpMessage{
	 	 $instance = new $target_classname();
	 	 return $instance->$target_method();
	 }
}
?>