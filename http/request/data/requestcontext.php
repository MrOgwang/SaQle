<?php
namespace SaQle\Http\Request\Data;

use SaQle\Http\Request\Data\Exceptions\KeyNotFoundException;
use SaQle\Http\Request\RequestIntent;

class RequestContext extends Data {
	 private array $pointers = [];
	 private bool  $session_active = false;

     public function activate_session(){
     	 $this->session_active = true;
     }

	 public function set(string $key, $value, $session = false){
	 	 $pointers[$key] = $session;

	 	 if($session && $this->session_active){
	 	 	 $_SESSION[$key] = $value;
	 	 	 return;
	 	 }

	 	 parent::set($key, $value);
	 }

	 public function get(string $key, $default = null){
	 	 if($this->session_active && array_key_exists($key, $_SESSION)){
	 	 	 return $_SESSION[$key];
	 	 }

	 	 return parent::get($key, $default);
     }

     public function get_or_fail(string $key) : mixed {

     	 if(!$this->exists($key)){
     	 	 throw new KeyNotFoundException($key);
     	 }

     	 return $this->get($key);
     }

     public function exists(string $key){
     	 if($this->session_active && array_key_exists($key, $_SESSION)){
	 	 	 return true;
	 	 }

     	 return parent::exists($key);
     }

     public function remove($key){
     	 if($this->session_active && array_key_exists($key, $_SESSION)){
     	 	 unset($_SESSION[$key]);
	 	 	 return true;
	 	 }
	 	 
	 	 return parent::remove($key);
	 }
}
