<?php
namespace SaQle\Controllers\Base;

use SaQle\Http\Response\HttpMessage;
use SaQle\Controllers\Refs\ControllerRef;
use Exception;

abstract class BaseController{
	 protected $request;

	 /**
	  * Specify the context keys you want passed down from parent 
	  * and whether to do that in api, web or all requests
	  * */
	 public ?array $pcontext = null {
	 	 set(?array $value){
	 	 	 $this->pcontext = $value;
	 	 }

	 	 get => $this->pcontext;
	 }

	 /**
	  * Specify the context keys you want passed along from sibling 
	  * and whether to do that in api, web or all requests
	  * */
	 protected $scontext;

	 /**
	  * A list of permission classes to enforce on controller
	  * @var array
	  * */
	 public protected(set) array $permissions = [] {
	 	set(array $value){
	 		 $this->permissions = $value;
	 	}

	 	get => $this->permissions;
	 }

	 /**
	  * The controller method that should be called by default
	  * if no target method has not been specified by a route
	  * 
	  * This is used by web routes to call the views for parent and default
	  * controllers
	  * 
	  * Defaults to get method
	  * 
	  * @var string
	  * */
	 protected string $index = 'get'; 

	 public function __construct(){
	 	 $this->request = resolve('request');
	 }

     /**
      * Get the name of the index method
      * 
      * @return string
      * */
	 public function get_index() : string {
	 	 return $this->index;
	 }

	 public function context_from_parent(array $keys, string $type = 'web'){
	 	 $this->pcontext = ['keys' => $keys, 'type' => $type];
	 }

	 public function reload($url = null){
	 	   if($url){
	 	   	  header('Location: '.$url);
	 	   }else{
	 	   	  header('Location: '.$this->current_url());
	 	   }
           exit;
	 }

	 public function __call($method, $args){
	 	 $allowed_methods = ['post', 'get', 'put', 'patch', 'delete', 'options'];
	 	 if(!in_array($method, $allowed_methods)){
         	 throw new Exception('The method '.$method.' is invalid for controller: '.$this::class);
         }

         return new HttpMessage(HttpMessage::OK);
	 }

	 /**
     * This method is called before controller method execution.
     * Override in child controllers to modify request input as needed.
     */
     public function on_method_start(array $input, string $method): array {
         return $input;
     }
}
