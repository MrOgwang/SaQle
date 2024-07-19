<?php
namespace SaQle\Controllers;

use SaQle\Http\Methods\Get\IGet;
use SaQle\Http\Methods\Post\IPost;
use SaQle\Http\Methods\Patch\IPatch;
use SaQle\Http\Methods\Delete\IDelete;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Controllers\Attributes\{ApiController, WebController};
use SaQle\Permissions\Utils\PermissionUtils;
use SaQle\Permissions\Attributes\Permissions;

abstract class IController implements IGet, IPost, IPatch, IDelete{
	 use PermissionUtils;
	 /**
	  * Request object
	  * @var Request
	  * */
	 protected Request $request;

	 /**
	  * Context array to pass to template
	  * @var array
	  * */
	 protected array   $context;

	 /**
	  * Keyword arguments passed to constructor
	  * @var array
	  * */
	 protected $kwargs;

	 /**
	  * A list of permission classes to enforce on controller
	  * @var array
	  * */
	 protected array $permissions = [];

	 /**
	  * Create a new controller instance
	  * @param Request $request : the request object
	  * @param array   $context : key => val array of the data to reolace in template
	  * @param array   $kwargs  : any other keyword arguments that maybe passed to constuctor
	  * */
	 public function __construct(Request $request, array $context = [], ...$kwargs){
	 	 $this->request     = $request;
	 	 $this->context     = $context;
	 	 $this->kwargs      = $kwargs;
	 }

	 public function get_request(){
	 	return $this->request;
	 }

     /**
      * Default post method implementation.
      * @return HttpMessage
      * */
	 public function post() : HttpMessage{
	 	//do nothing
	 	return new HttpMessage(StatusCode::OK);
	 }

	 /**
	  * Default get method implementation
	  * @return HttpMessage
	  * */
	 public function get() : HttpMessage{
	 	//do nothing
	 	return new HttpMessage(StatusCode::OK);
	 }

	 /**
	  * Default patch method implementation
	  * @return HttpMessage
	  * */
	 public function patch() : HttpMessage{
	 	//do nothing
	 	return new HttpMessage(StatusCode::OK);
	 }

	 /**
	  * Default delete method implementation
	  * @return HttpMessage
	  * */
	 public function delete() : HttpMessage{
	 	//do nothing
	 	return new HttpMessage(StatusCode::OK);
	 }

	 /**
	  * Return the path for a desired template for this controller. If provided, this will override the default template set
	  * on WebController attribute.
	  * */
	 public function get_desired_template(){
	 	return null;
	 }

	 /**
	  * Return the path for a desired parent template for this controller. If provided, this will override the default parent template set
	  * on ParentTemplate attribute.
	  * */
	 public function get_desired_parent_template(){
	 	return null;
	 }

	 public function web_instance(){
	 	 $reflector  = new \ReflectionClass($this::class);
		 $attributes = $reflector->getAttributes(WebController::class);
		 if(!$attributes){
		 	throw new \Exception('This is not a web controller. Add a web controller attribute in the definition to make this a web controller.');
		 }

         //evaluate controller permissions if there are any
         if($this->permissions){
         	 [$result, $redirect_url] = $this->evaluate_permissions($this->permissions, true, $this->request);
	         if(!$result){
	             header("Location: ".$redirect_url);
	         }
         }

         //get controller method to be called
         $target_classname = $this->request->final_route->get_target()[0];
         $target_parts     = explode("@", $target_classname);
         $method           = $target_parts[1] ?? strtolower($this->request->final_route->get_method());
         if(!method_exists($this::class, $method)){
         	 throw new \Exception('The target action was not found on the target controller!');
         }

         //evaluate permissions declared on the method by Permissions attribute if any.
         $reflection_method = $reflector->getMethod($method);
         $permissions_attrs = $reflection_method->getAttributes(Permissions::class);
         if($permissions_attrs){
         	 $_instance = $permissions_attrs[0]->newInstance();
         	 [$result, $redirect_url] = $this->evaluate_permissions($_instance->get_permissions(), true, $this->request);
	         if(!$result){
	             header("Location: ".$redirect_url);
	         }
         }

	 	 $http_message  = $this->$method();
	 	 $response_data = $http_message->get_response() ?? [];
	     $web_instance  = $attributes[0]->newInstance();

	     //override default template if the controller has defined a desired template,
	     $dtemplate     = $this->get_desired_template();
		 if($dtemplate){
		 	$web_instance->set_template($dtemplate);
		 }
		 $web_instance->init($this->request, $response_data);
		 return $web_instance;
	 }

	 public function api_instance(){
	 	 $http_message = null;
	 	 $reflector    = new \ReflectionClass($this::class);
		 $attributes   = $reflector->getAttributes(ApiController::class);
		 $_instance    = new ApiController();
		 if(!$attributes){
		 	 $http_message =  new HttpMessage(
			 	 code:    StatusCode::INTERNAL_SERVER_ERROR, 
			 	 message: 'This is not an api controller. Add a api controller attribute in the definition to make this an api controller.'
			 );
			 $_instance->init($this->request, $http_message);
			 return $_instance;
		 }

		 //evaluate controller permissions if there are any
         if($this->permissions){
         	 [$result, $redirect_url] = $this->evaluate_permissions($this->permissions, true, $this->request);
	         if(!$result){
	             $http_message =  new HttpMessage(
			 	 	code:    StatusCode::FORBIDDEN, 
			 	 	message: 'Access denied for the resource or operation requested!'
			 	 );
			 	 $_instance->init($this->request, $http_message);
			     return $_instance;
	         }
         }

         //get controller method to be called
         $target_classname = $this->request->final_route->get_target()[0];
         $target_parts     = explode("@", $target_classname);
         $method           = $target_parts[1] ?? strtolower($this->request->final_route->get_method());
         if(!method_exists($this::class, $method)){
         	 $http_message =  new HttpMessage(
		 	 	code:    StatusCode::INTERNAL_SERVER_ERROR, 
		 	 	message: 'The target action was not found on the target controller!'
		 	 );
		 	 $_instance->init($this->request, $http_message);
			 return $_instance;
         }

         //evaluate permissions declared on the method by Permissions attribute if any.
         $reflection_method = $reflector->getMethod($method);
         $permissions_attrs = $reflection_method->getAttributes(Permissions::class);
         if($permissions_attrs){
         	 [$result, $redirect_url] = $this->evaluate_permissions(($permissions_attrs[0]->newInstance())->get_permissions(), true, $this->request);
	         if(!$result){
	             $http_message =  new HttpMessage(
			 	 	code:    StatusCode::FORBIDDEN, 
			 	 	message: 'Access denied for the resource or operation requested'
			 	 );
			 	 $_instance->init($this->request, $http_message);
			     return $_instance;
	         }
         }

         $api_instance  = $attributes[0]->newInstance();
         $api_instance->init($this->request, $this->$method());
		 return $api_instance;
	 }

	 public function current_url(){
	 	 $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
	 	 return $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	 }

	 public function reload(){
           header('Location: '.$this->current_url());
           exit;
	 }
}
?>