<?php
namespace SaQle\Controllers\Base;

use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Controllers\Helpers\DefaultHttpHandlers;
use SaQle\Controllers\Refs\ControllerRef;

abstract class BaseController{
	 use DefaultHttpHandlers;
	 
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

	 public function current_url(){
	 	 $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
	 	 return $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	 }

	 public function reload($url = null){
	 	   if($url){
	 	   	  header('Location: '.$url);
	 	   }else{
	 	   	  header('Location: '.$this->current_url());
	 	   }
           exit;
	 }

	  public function expose_controller(string $name, string $class){
	 	 $refs = ControllerRef::init();
	 	 $refs::register([$name => $class]);
	 }
}
?>