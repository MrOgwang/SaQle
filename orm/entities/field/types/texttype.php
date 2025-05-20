<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Scalar;
use SaQle\Orm\Entities\Field\Interfaces\IField;

class TextType extends Scalar implements IField{
	 //whether to allow numbers inside text
	 public protected(set) bool $strict = false {
	 	 set(bool $value){
	 	 	 $this->strict = $value;
	 	 }

	 	 get => $this->strict;
	 }

	 //whether to allow empty strings
	 public protected(set) bool $empty = true {
	 	 set(bool $value){
	 	 	 $this->empty = $value;
	 	 }

	 	 get => $this->empty;
	 }

	 public function __construct(...$kwargs){
	 	 $kwargs['validation_type'] = 'text';
	 	 $kwargs['primitive_type']  = "string";
		 parent::__construct(...$kwargs);
	 }

	 protected function get_validation_kwargs() : array{
		 return array_merge(parent::get_validation_kwargs(), ['strict', 'empty']);
	 }
}
