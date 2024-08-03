<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class DateTimeField extends TextType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "DATETIME";
		$kwargs['vtype'] = "text";
		$kwargs['ctype'] = FormControlTypes::DATETIMELOCAL->value;
		$kwargs['ptype'] = "string";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = 30;
		$kwargs['max']    = 30;

		parent::__construct(...$kwargs);
	}
}
?>