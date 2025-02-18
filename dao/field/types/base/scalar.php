<?php
namespace SaQle\Dao\Field\Types\Base;

abstract class Scalar extends Simple{
	 //an array of choices from which the value must exists
	 public ?array $choices = null {
	 	 set(?array $value){
	 	 	 if(is_array($value) && array_is_list($value)){
	 	 	 	 $new_value = [];
	 	 	 	 foreach($value as $v){
	 	 	 	 	 $new_value[$v] = ucwords($v);
	 	 	 	 }
	 	 	 	 $this->choices = $new_value;
	 	 	 }else{
	 	 	 	 $this->choices = $value;
	 	 	 }
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
 
	 public function __construct(...$kwargs){
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