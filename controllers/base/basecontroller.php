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

	 public function __construct(){
	 	 $this->request = resolve('request');
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
	 	 $allowed_methods = ['post', 'get', 'put', 'patch'];
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
