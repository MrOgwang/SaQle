<?php
namespace SaQle\Controllers;

use SaQle\Http\Methods\Get\IGet;
use SaQle\Http\Methods\Post\IPost;
use SaQle\Http\Methods\Patch\IPatch;
use SaQle\Http\Methods\Delete\IDelete;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Services\Container\Cf;
use SaQle\Controllers\Refs\ControllerRef;

abstract class IController implements IGet, IPost, IPatch, IDelete{
	 protected $request;

	 /**
	  * The name of the template file
	  * */
	 protected string $template;

	 /**
	  * A list of permission classes to enforce on controller
	  * @var array
	  * */
	 protected array $permissions = [];

	 public function __construct(){
	 	 $this->request = Cf::create('request');
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
	  * Return the name of the template for controller
	  * */
	 public function get_template(){
	 	return null;
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

	 public function get_permissions() : array{
	 	 return $this->permissions;
	 }

	 public function expose_controller(string $name, string $class){
	 	 $refs = ControllerRef::init();
	 	 $refs->add($name, $class);
	 }
}
?>