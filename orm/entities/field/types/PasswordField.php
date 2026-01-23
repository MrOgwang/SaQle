<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class PasswordField extends TinyTextField {
	 //whether to mask input or not
	 public private(set) bool $mask = true {
	 	 set(bool $value){
	 	 	 $this->mask = $value;
	 	 }

	 	 get => $this->mask;
	 }

	 public function mask(bool $mask){
	 	 $this->mask = $mask;
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type' => $this->mask ? 'password' : 'text',
	 	 ]);
	 }
	 
}
