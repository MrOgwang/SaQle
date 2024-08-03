<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class DateField extends TextType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "DATE";
		$kwargs['vtype'] = "text";
		$kwargs['ctype'] = FormControlTypes::DATE->value;
		$kwargs['ptype'] = "string";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = 10;
		$kwargs['max']    = 10;

		parent::__construct(...$kwargs);
	}
}
?>