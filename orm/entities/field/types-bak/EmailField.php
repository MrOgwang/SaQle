<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class EmailField extends TinyTextField {
	 protected function get_validation_kwargs() : array {
		 return array_merge(parent::get_validation_kwargs(), ['email']);
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type' => 'email',
	 	 ]);
	 }
	 
}
