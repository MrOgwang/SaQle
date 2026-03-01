<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Database\ColumnType;
use RuntimeException;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class ChoiceField extends CharField {
	 //the choices to pick from
	 #[ShouldValidate()]
	 protected ?array $choices = null {
	 	 set(?array $value){

	 	 	 //assert choices
		 	 if(!isset($value) || !is_array($value) || empty($value)){
		 	 	 throw new RuntimeException('Choices must be provided for a choice field!');
		 	 }

	         //if choices is a list
		 	 if(array_keys($value) === range(0, count($value) - 1)){
		 	 	 $this->type = ColumnType::INTEGER;
		 	 }else{ //if choices is a map
		 	 	 $this->type = ColumnType::CHAR;
		 	 }

	 	 	 $this->choices = $value;
	 	 }

	 	 get => $this->choices;
	 }

	 //whether to pick multiple choices
	 protected bool $multiple = false;

	 //the human readable label for particular choice
	 public mixed $label {
	 	 get {
	 	 	return $this->choices[$this->value] ?? $this->value;
	 	 }
	 }

	 protected bool $_use_keys = false;

	 public function choices(array $choices){
	 	 $this->choices = $choices;
	 	 return $this;
	 }

	 public function get_choices(){
	 	 return $this->choices;
	 }

	 public function multiple(bool $multiple = true){
	 	 $this->multiple = true;
	 	 return $this;
	 }

	 public function is_multiple(){
	 	 return $this->multiple;
	 }

	 public function use_keys(bool $use_keys = true){
	 	 if(!$this->choices){
	 	 	 throw new RuntimeException("Use keys cannot be called before specifying choices!");
	 	 }

	 	 $this->choices = array_keys($this->choices);

	 	 $this->_use_keys = $use_keys;
	 	 
	 	 return $this;
	 }
}

