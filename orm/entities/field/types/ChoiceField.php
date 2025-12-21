<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class ChoiceField extends TinyTextField {
	 public function __construct(...$kwargs){
		 $kwargs['length']         = 255;
		 $kwargs['maximum']        = 255;
		 parent::__construct(...$kwargs);
	 }

	 //an array of choices from which the value must exists
	 public ?array $choices = null {
	 	 set(?array $value){
	 	 	$this->choices = $value;
	 	 }

	 	 get => $this->choices;
	 }

	 /**
	  * When choices are provided as a key => value array, 
	  * the keys are what is actually stored in the databased and returned when render method is called.
	  * 
	  * The description property returns the use friendly value for a given choice. The description
	  * is a virtual property and cannot be set.
	  * */
	 public mixed $description {
	 	 get {
	 	 	return $this->choices[$this->value] ?? $this->value;
	 	 }
	 }

	 /**
	  * Whether to allow multiple choices
	  * */
	 public protected(set) bool $multiple = false {
	 	 set(bool $value){
	 	 	 $this->multiple = $value;
	 	 }

	 	 get => $this->multiple;
	 }

	 protected function get_validation_kwargs() : array{
		 return array_merge(parent::get_validation_kwargs(), ['choices']);
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type'       => 'select',
	 	 	 'multiple'   => $this->multiple,
	 	 	 'options'    => $this->choices
	 	 ]);
	 }

	 //set multiple
	 public function multiple(bool $multiple = true){
	 	 $this->multiple = $multiple;
	 	 return $this;
	 }
}
