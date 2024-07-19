<?php
namespace SaQle\Http\Request\Data;

use SaQle\Http\Request\Data\Exceptions\KeyNotFoundException;

class Data{
	 private array $data;
	 public function __construct(){
		 $this->data = [];
	 }
	 public function set(string $key, $value){
	 	$this->data[$key] = $value;
	 }
	 public function get(string $key, $default = null){
		 
		 $default = array_key_exists($key, $this->data) ? $this->data[$key] : $default;
		 if(is_null($default)){
			 throw new KeyNotFoundException($key);
		 }
		 
		 return $default;
     }

     private function collect_keys_and_defaults(array $keys, $defaults){
     	$collected = [];
     	foreach($keys as $i => $k){
     		 $collected[$k] = is_array($defaults) ? ($defaults[$i] ?? ($defaults[count($defaults) - 1] ?? null)) : $defaults;
     	}
     	return $collected;
     }

     public function get_all(array $keys, bool $all_must_exist = false, $defaults = null, $keep_keys = false) : array{
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
}
?>