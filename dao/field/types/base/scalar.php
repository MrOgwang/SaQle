<?php
namespace SaQle\Dao\Field\Types\Base;

abstract class Scalar extends RealField{
	 private function is_explicit_associative_array($array){
	     if(!is_array($array)){
	         return false;
	     }

	     foreach ($array as $key => $value) {
	         //Check if the key is a string or if it's a numeric string (explicitly defined)
	         if(is_string($key) || (string)(int)$key === (string)$key && $key !== (int)$key){
	             return true;
	         }
	     }

	     return false;
	 }

	 private function format_choices(array $choices, bool $use_keys = false) : array{
	 	 if(!$this->is_explicit_associative_array($choices)){
 	 	 	 $new_values = [];
 	 	 	 foreach($choices as $i => $v){
 	 	 	 	 $new_values[$v] = !$use_keys ? ucwords($v) : $i;
 	 	 	 }
 	 	 	 return $new_values;
 	 	 }else{
 	 	 	 return $choices;
 	 	 }
	 }

	 //an array of choices from which the value must exists
	 public ?array $choices = null {
	 	 set(?array $value){
	 	 	$this->choices = $value;
	 	 }

	 	 get => $this->choices;
	 }

	 //the default value to use if the value is not provided
	 public mixed $default = null {
	 	 set(mixed $value){
	 	 	 $this->default = $value;
	 	 }

	 	 get => $this->default;
	 }

	 protected bool $use_keys = false;
 
	 public function __construct(...$kwargs){
	 	 if(array_key_exists('choices', $kwargs) && is_array($kwargs['choices'])){
	 	 	 $kwargs['choices'] = $this->format_choices($kwargs['choices'], $kwargs['use_keys'] ?? false);
	 	 }
		 parent::__construct(...$kwargs);
	 }

	 protected function get_validation_kwargs() : array{
		 return array_merge(parent::get_validation_kwargs(), ['choices']);
	 }

	 protected function get_db_kwargs() : array{
		 return array_merge(parent::get_db_kwargs(), ['default']);
	 }
}
?>