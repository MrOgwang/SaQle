<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class TextField extends TextType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "TEXT";
		$kwargs['vtype'] = "text";
		$kwargs['ctype'] = FormControlTypes::TEXTAREA->value;
		$kwargs['ptype'] = "string";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = isset($kwargs['length']) ? $kwargs['length'] : 65535;
		$kwargs['max']    = isset($kwargs['max']) ? $kwargs['max'] : 65535;

		/**
		 * Fill in control props.
		 * */
		$kwargs['multiline'] = true;

		parent::__construct(...$kwargs);
	}
}
?>