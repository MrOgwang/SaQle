<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class TimeField extends TextType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "TIME";
		$kwargs['vtype'] = "text";
		$kwargs['ctype'] = FormControlTypes::TIME->value;
		$kwargs['ptype'] = "string";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = 50;
		$kwargs['max']    = 50;

		parent::__construct(...$kwargs);
	}
}
?>