<?php
namespace SaQle\Http\Request\Processors;

use SaQle\Services\Container\Cf;
use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Request\Data\Sources\{FromPath, FromForm, FromDb};
use SaQle\Http\Request\Data\Sources\Managers\HttpDataSourceManager;
use ReflectionMethod;
use ReflectionParameter;

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
	 protected function get_target_method(string $controller, string $action) : array{
         if(!method_exists($controller, $action)){
         	 throw new \Exception('The target action '.$action.' was not found on the target controller! '. $controller);
         }

         return [$controller, $action];
	 }

	 /**
	  * Call the controllers target method to return a key => value array response.
	  * 
	  * @param string $target
	  * @return array $response_data;
	  * */
	 public function get_target_response(string $target_classname, string $target_method, ?array $parent_context = null) : array{
	 	 $instance      = new $target_classname();
	 	 $pcontext      = $instance->pcontext;
	 	 $ctxfromparent = [];
	 	 if($parent_context && $pcontext){
	 	 	 $pcn = explode("\\", $this::class);
	 	 	 $processor = strtolower(end($pcn));
	 	 	 if(str_starts_with($processor, $pcontext['type'])){
	 	 	 	 foreach($pcontext['keys'] as $ck){
	 	 	 	 	 if(array_key_exists($ck, $parent_context)){
	 	 	 	 	 	 $ctxfromparent[$ck] = $parent_context[$ck];
	 	 	 	 	 }
	 	 	 	 }
	 	 	 }
	 	 }

	 	 return [$this->call_target_method($instance, $target_method), $ctxfromparent];
	 }

	 private function call_target_method($target_instance, $method){
         $reflection_method = new ReflectionMethod($target_instance::class, $method);
         $parameters        = $reflection_method->getParameters();
         $args              = [];

	     foreach ($parameters as $param){
	     	 $param_name  = $param->getName();
	         $param_type  = $param->getType();
	         $default_val = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
	         $optional    = $param->isOptional();
		     $sourceattr  = $param->getAttributes()[0] ?? null;

		     if($sourceattr){
		     	 $attribute_instance = $sourceattr->newInstance();
	             $value = new HttpDataSourceManager(
	             	 $attribute_instance, ...[
	             	 	'name'     => $param_name,
	             	 	'type'     => $param_type,
	             	 	'default'  => $default_val,
	             	 	'optional' => $optional
	             	 ]
	             )->get_value();
	             $args[] = $value;
		     }else{
		     	 $args[] = $optional ? $this->request->data->get($param_name) : $this->request->data->get_or_fail($param_name);
		     }
	     }
	     
	     return $reflection_method->invokeArgs($target_instance, $args);
     }
}
?>