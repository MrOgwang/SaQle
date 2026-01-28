<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\Base\RealField;

class TextType extends RealField implements IField{
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
		 parent::__construct(...$kwargs);
	 }

	 protected function get_validation_kwargs() : array {
		 return array_merge(parent::get_validation_kwargs(), ['strict', 'empty']);
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type' => 'text',
	 	 ]);
	 }

	 //set strict
	 public function strict(bool $strict = true){
	 	 $this->strict = $strict;
	 	 return $this;
	 }

	 //set empty
	 public function empty(bool $empty = true){
	 	 $this->empty = $empty;
	 	 return $this;
	 }
	 
}
