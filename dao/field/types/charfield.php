<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class CharField extends TextType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "CHAR";
		$kwargs['vtype'] = "text";
		$kwargs['ctype'] = FormControlTypes::TEXT->value;
		$kwargs['ptype'] = "string";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = 1;
		$kwargs['max'] = 1;

		/**
		 * Fill in control props.
		 * */
		$kwargs['multiline'] = false;

		parent::__construct(...$kwargs);
	}
}
?>