<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\Base\RealField;

class NumberType extends RealField  implements IField{
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

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type' => 'number',
	 	 ]);
	 }
	 
}
