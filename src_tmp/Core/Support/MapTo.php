<?php

namespace SaQle\Core\Support;

/**
 * The MapTo attribute is used on controller method parameters and request contract properties.
 * 
 * The MapTo attribute maps a contract property to a model field:
 * 
 * */

use Attribute;
use RuntimeException;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class MapTo {
	 
	 /**
	  * The model class
	  * */
	 public private(set) string $model {
	 	 set(string $value){
	 	 	 $this->model = $value;
	 	 }

	 	 get => $this->model;
	 }

	 /**
	  * The model field. If a property maps to several fields,
	  * separate them with commas.
	  * 
	  * Example: Suppose a request contract property or controller method param is called full_name, 
	  *          but the User model has fields first_name and last_name, then this becomes
	  *          "first_name,last_name"
	  * */
	 public private(set) ?string $field = null {
	 	 set(?string $value){
	 	 	 $this->field = $value;
	 	 }

	 	 get => $this->field;
	 }

	 /**
	  * If a property maps to several fields, this is the glue
	  * that holds the value together
	  * 
	  * Example: fullname(first_name last_name) the glue is a space
	  * */
	 public private(set) ?string $glue = null {
	 	 set(?string $value){
	 	 	 $this->glue = $value;
	 	 }

	 	 get => $this->glue;
	 }

	 public function __construct(string $model, ?string $field = null, ?string $glue = null){
	 	 $this->model = $model;
	 	 $this->field = $field;
	 	 $this->glue  = $glue;
	 }

	 public function field(string $field){
	 	 $this->field = $field;
	 }

	 public function get_validation_rules() : array {

	 	 if(!$this->field || !$this->model){
	 	 	 return [];
	 	 }

	 	 $model_class = $this->model;
	 	 $field_names = explode(",", $this->field);

	 	 $rules = [];

	 	 foreach($field_names as $field_name){

	 	 	 $field_name = trim($field_name);

	 	 	 $field = $model_class::$field_name();

	 	 	 $rules = array_merge($rules, $field->get_validation_rules());

	 	 }

	 	 return array_filter($rules, fn($r) => $r !== null);
	 }

	 public function is_spread(){

	 	 $field_names = explode(",", $this->field);

	 	 return count($field_names) > 1;

	 }

	 public function get_field(){

	 	 if($this->is_spread()){
	 	 	 throw new RuntimeException('Mapping is spread across two or more fields!');
	 	 }

	 	 $model_class = $this->model;
	 	 $field_name  = $this->field;

         return $model_class::$field_name();

	 }
	
}
