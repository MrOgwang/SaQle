<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class VarCharField extends TextType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "VARCHAR";
		$kwargs['vtype'] = "text";
		$kwargs['ctype'] = FormControlTypes::TEXT->value;
		$kwargs['ptype'] = "string";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = isset($kwargs['length']) ? $kwargs['length'] : 255;
		$kwargs['max'] = isset($kwargs['max']) ? $kwargs['max'] : $kwargs['length'];

		/**
		 * Fill in control props.
		 * */
		$kwargs['multiline'] = false;

		parent::__construct(...$kwargs);
	}
}
?>