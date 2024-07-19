<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;

class Text extends Scalar implements IField{
	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected function get_validation_properties(){
		return array_merge(parent::get_validation_properties(), [
			/**
			 * Whether to allow digits embedded in text or not.
			 * */
			'strict' => 'is_strict',

			/**
			 * Whether to allow empty strings or not.
			 * */
			'empty' => 'allow_empty'
		]);
	}

	protected function get_control_properties(){
		return array_merge(parent::get_control_properties(), [
			'multiline' => 'multiline'
		]);
	}
}
?>