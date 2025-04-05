<?php
namespace SaQle\Http\Request\Data;

use SaQle\Http\Request\Data\Exceptions\KeyNotFoundException;

class Data{
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
     	 	 throw new KeyNotFoundException($key);
     	 }

     	 return $this->data[$key];
     }

     private function collect_keys_and_defaults(array $keys, $defaults){
     	$collected = [];
     	foreach($keys as $i => $k){
     		 $collected[$k] = is_array($defaults) ? ($defaults[$i] ?? ($defaults[count($defaults) - 1] ?? null)) : $defaults;
     	}
     	return $collected;
     }

     public function get_many(array $keys, bool $all_must_exist = false, $defaults = null, $keep_keys = false) : array{
     	 $keys    = array_is_list($keys) ? $this->collect_keys_and_defaults($keys, $defaults) : $keys;
     	 $results = [];
     	 if(!$keep_keys){
     	 	 foreach($keys as $i => $k){
	     	 	 $results[] = $this->get(key: $i, default: !$all_must_exist ? (is_null($k) ? '' : $k) : $k);
	     	 }
     	 }else{
     	 	 foreach($keys as $i => $k){
	     	 	 $results[$i] = $this->get(key: $i, default: !$all_must_exist ? (is_null($k) ? '' : $k) : $k);
	     	 }
     	 }
     	 return $results;
     }

     public function exists(string $key){
     	 return array_key_exists($key, $this->data);
     }

     public function get_all(){
     	 return $this->data;
     }
}
?>