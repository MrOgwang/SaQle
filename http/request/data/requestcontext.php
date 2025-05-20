<?php
namespace SaQle\Http\Request\Data;

use SaQle\Http\Request\Data\Exceptions\KeyNotFoundException;

class RequestContext extends Data {
	 private array $pointers = [];

	 public function set(string $key, $value, $session = false){
	 	 $pointers[$key] = $session;
	 	 if(!$session){
	 	 	 parent::set($key, $value);
	 	 }else{
	 	 	 $_SESSION[$key] = $value;
	 	 }
	 }

	 public function get(string $key, $default = null){

	 	 if($_SESSION && array_key_exists($key, $_SESSION)){
	 	 	 return $_SESSION[$key];
	 	 }

	 	 return parent::get($key, $default);
     }

     public function get_or_fail(string $key) : mixed {
     	 if(($_SESSION && !array_key_exists($key, $_SESSION)) && !array_key_exists($key, $this->data)){
	 	 	 throw new KeyNotFoundException($key);
	 	 }

	 	 if($_SESSION && array_key_exists($key, $_SESSION)){
	 	 	 return $_SESSION[$key];
	 	 }else{
	 	 	return $this->data[$key];
	 	 }
     }

     public function exists(string $key){
     	 if($_SESSION && array_key_exists($key, $_SESSION))
     	 	 return true;

     	 return parent::exists($key);
     }

     public function remove($key){
	 	 if($_SESSION && array_key_exists($key, $_SESSION)){
	 	 	 unset($_SESSION[$key]);

	 	 	 return true;
	 	 }

	 	 return parent::remove($key);
	 }
}
