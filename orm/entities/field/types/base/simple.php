<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Orm\Entities\Field\Interfaces\IField;

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

	 //The actual, unmodified value of this field
	 public mixed $value = null {
	 	 set(mixed $val){
	 	 	 $this->value = $val;
	 	 }

	 	 get => $this->value;
	 }

	 /**
	  * When calling the render method of a field, a data object/array containing
	  * all the fields in a model and their corresponding values is passed to the render method.
	  * 
	  * This is because the render method may need values of other model fields
	  * to construct its own value
	  * 
	  * This data object/array is stored in context
	  * 
	  * */
	 public mixed $context = null {
	 	 set(mixed $value){
	 	 	 $this->context = $value;
	 	 }

	 	 get => $this->context;
	 }

     /**
      * The field needs to have knowledge of the model in which it is defined. This array will
      * contain information concerning the model.
      * 
      * The information contained here include:
      * 
      * 1. The model class name
      * 2. The model primary key name
      * 3. The model instance primary key value
      * */
	 public array $model_info = [] {
	 	 set(array $value){
	 	 	 $this->model_info = $value;
	 	 }

	 	 get => $this->model_info;
	 }

	 /**
      * This function will be called to modify a field value before it is displayed
      * Override this function to display formatted values for a given field
      * 
      * @param array $model_info: an array of model information
      * - model_name, pk_value, field_name
      * */
	 public function render() : mixed {
	 	 if(!is_null($this->value))
	 	 	 return $this->value;

	 	 return $this->default;
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

	 public function __toString() {
         return $this->render($this->context);
     }
}
