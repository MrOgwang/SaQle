<?php

namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Interfaces\IField;

abstract class Simple implements IField{
	 //The name of the field as defined in the model
	 public string $field_name = '' {
	 	 set(string $value){
	 	 	 $this->field_name = $value;
	 	 	 if(!$this->column_name){
	 	 	 	 $this->column_name = $value;
	 	 	 }
	 	 }

	 	 get => $this->field_name;
	 }

	 //The name of the table column to associate with this field
	 public protected(set) string $column_name = '' {
	 	 set(string $value){
	 	 	 $this->column_name = $value;
	 	 }

	 	 get => $this->column_name;
	 }

     //initialize a new field
	 public function __construct(...$kwargs){
	 	 foreach($kwargs as $k => $v){
	 	 	 $this->$k = $v;
	 	 }
	 }

	 abstract public function get_validation_configurations() : array;
	 abstract public function get_field_definition() : string | null;

	 public function get_kwargs() : array{
	 	 return [];
	 }

     /**
      * This function will be called to modify a field value before it is displayed
      * Override this function to display formatted values for a given field
      * */
	 public function render(mixed $data) : mixed{
	 	 return $data[$this->field_name] ?? null;
	 }
}
?>