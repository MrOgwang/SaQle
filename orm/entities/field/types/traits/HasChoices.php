<?php

namespace SaQle\Orm\Entities\Field\Types\Traits;

use SaQle\Orm\Database\ColumnType;
use RuntimeException;
use SaQle\Orm\Entities\Field\Attributes\{
	 FieldDefinition, 
	 ShouldValidate,
	 FormControl
};

trait HasChoices {

     #[FormControl('choices')]
	 protected ?array $raw_choices = null;

	 #[ShouldValidate()]
	 protected ?array $choices = null {
	 	 set(?array $value){

		 	 if(!$value){
		 	 	 throw new RuntimeException('Choices must be provided for a choice field!');
		 	 }

	 	 	 $this->choices = $value;
	 	 }

	 	 get => $this->choices;
	 }

	 //whether to pick multiple choices
	 #[FormControl()]
	 protected bool $multiple = false;

	 //the human readable label for particular choice
	 public mixed $label {
	 	 get {
	 	 	return $this->raw_choices[$this->value] ?? $this->value;
	 	 }
	 }
	 
	 public function choices(array $choices){
	 	 $this->choices = $choices;
	 	 return $this;
	 }

	 public function raw_choices(array $choices){
	 	 $this->raw_choices = $choices;
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

	 protected function initialize_defaults(){
	 	 if(!$this->control_type){
	 	 	 
	 	 	 if($this->multiple){
	 	 	 	 $this->control_type = "checkbox";
	 	 	 }else{
	 	 	 	 $this->control_type = count($this->choices) > 5 ? "select" : "radio";
	 	 	 }

	 	 }

		 parent::initialize_defaults();
     }
}

