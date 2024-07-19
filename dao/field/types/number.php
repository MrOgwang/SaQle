<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;

class Number extends Scalar implements IField{
	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected function get_validation_properties(){
		return array_merge(parent::get_validation_properties(), [
			/**
			 * Whether to allow negative numbers or not
			 * */
			'absolute' => 'is_absolute',

			/**
			 * Whether to allow zero values or not
			 * */
			'zero' => 'allow_zero'
		]);
	}
}
?>