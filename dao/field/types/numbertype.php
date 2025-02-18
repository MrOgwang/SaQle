<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;

class NumberType extends Scalar implements IField{
	 //whether negative numbers are allowed
	 public protected(set) bool $absolute = false {
	 	 set(bool $value){
	 	 	 $this->absolute = $value;
	 	 }

	 	 get => $this->absolute;
	 }

     //whether zero is allowed
	 public protected(set) bool $zero = true {
	 	 set(bool $value){
	 	 	 $this->zero = $value;
	 	 }

	 	 get => $this->zero;
	 }

	 public function __construct(...$kwargs){
	 	 $kwargs['validation_type'] = 'number';
	 	 $kwargs['primitive_type']  = "int"; //this will be overriden for floats and doubles
		 parent::__construct(...$kwargs);
	 }

	 protected function get_validation_kwargs(): array {
		 return array_merge(parent::get_validation_kwargs(), ['absolute', 'zero']);
	 }
}
?>