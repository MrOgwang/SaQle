<?php
namespace SaQle\Http\Request\Processors;

use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Request\Data\Sources\{From, FromPath, FromForm, FromDb};
use SaQle\Http\Request\Data\Sources\Managers\HttpDataSourceManager;
use SaQle\Controllers\Helpers\{RespondWith, Exceptions, ExceptionHandler, OnErrorResponse};
use SaQle\Auth\Models\Attributes\AuthUser;
use SaQle\Auth\Permissions\AccessControl;
use SaQle\Core\Services\Helpers\ObservedService;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

class RequestProcessor{
	 protected $request;

	 public function __construct(){
	 	 $this->request = resolve('request');
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
	 	 try{
	 	 	 //echo $target_instance::class." ".$method."\n\n";
	 	 	 if(!method_exists($target_instance, $method)){
	 	 	 	 return ok();
             }

	 	 	 $reflection_method = new ReflectionMethod($target_instance::class, $method);

	 	 	 //extract exceptions from the method's attribute
	         $exceptions_attr   = $reflection_method->getAttributes(Exceptions::class);
	         $custom_exceptions = $exceptions_attr ? $exceptions_attr[0]->newInstance()->exceptions : [];

	 	 	 //extract access control guards
             $access_attr       = $reflection_method->getAttributes(AccessControl::class);
             $access_control    = $access_attr ? $access_attr[0]->newInstance() : null;
             if($access_control){
             	 $access_control->enforce();
             }

             //extract response types if any
             $responds_attr     = $reflection_method->getAttributes(RespondWith::class);
             $custom_response   = $responds_attr ? $responds_attr[0]->newInstance() : null;

             $parameters        = $reflection_method->getParameters();
	         $args              = [];
		     foreach ($parameters as $param){
		     	 $param_name   = $param->getName();
		         $param_type   = $param->getType();
		         $param_type   = !is_null($param_type) ? str_replace('?', '', $param_type) : $param_type;
		         $default_val  = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
		         $optional     = $param->isOptional();
			     $sourceattr   = $param->getAttributes()[0] ?? null;
			     $attrinstance = $sourceattr ? $sourceattr->newInstance() : null;

			     if($attrinstance){
			     	 if($attrinstance instanceof From){
			     	 	 $args[] = new HttpDataSourceManager($attrinstance, ...['name' => $param_name, 'type' => $param_type, 'default' => $default_val, 'optional' => $optional])->get_value();
			     	 }elseif($attrinstance instanceof AuthUser){
			     	 	 $args[] = $this->request->user;
			     	 }elseif($attrinstance instanceof ObservedService){
			     	 	 $actual_service = resolve($attrinstance->service);
			     	 	 $args[] = $actual_service;
			     	 }
			     }elseif($param_type && class_exists($param_type)){
                     $args[] = resolve($param_type);
                 }else{
                 	 //check route params, then query, then data
                 	 $value = $this->request->route->params->get(
                 	 	 $param_name, 
                 	 	 $this->request->route->queries->get(
                 	 	 	 $param_name, 
                 	 	 	 $optional ? $this->request->data->get($param_name, $default_val) : $this->request->data->get_or_fail($param_name)
                 	 	 )
                 	 );
                     $args[] = $value;
                 }
		     }
		     $http_message = $reflection_method->invokeArgs($target_instance, $args);

		     //override the context with custom response if any.
		     if($custom_response){
		     	 $http_message->set_data($custom_response->get_context());
		     }

		     return $http_message;

	 	 }catch(Throwable $e){
	 	 	 //print_r($e);
	 	 	 //extract any error responses set on the method
	         $errresponse_attr = $reflection_method->getAttributes(OnErrorResponse::class);
	         $errresponse      = $errresponse_attr ? $errresponse_attr[0]->newInstance() : null;
	 	 	 return ExceptionHandler::handle($e, $custom_exceptions, $this->request->is_web_request(), $errresponse);
	 	 }  
     }
}
?>