<?php
namespace SaQle\Http\Request\Data;

use SaQle\Core\Exceptions\Data\KeyNotFoundException;

class Data {

	 protected array $data;

	 public function __construct(){
		 $this->data = [];
	 }

	 public function remove($key){
	 	 if(array_key_exists($key, $this->data)){
	 	 	 unset($this->data[$key]);
	 	 }

	 	 return true;
	 }

	 public function set(string $key, $value){
	 	 $this->data[$key] = $value;
	 }

	 public function get(string $key, $default = null){
	 	 return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
     }

     public function get_or_fail(string $key) : mixed {
     	 if(!array_key_exists($key, $this->data)){
     	 	 throw new KeyNotFoundException(context: ['type' => 'Data', 'key' => $key]);
     	 }

     	 return $this->data[$key];
     }

     public function exists(string $key){
     	 return array_key_exists($key, $this->data);
     }

     public function get_all(){
     	 return $this->data;
     }
}
