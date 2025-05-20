<?php
namespace SaQle\Orm\Entities\Field\Types\Base;

abstract class Scalar extends RealField{
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
