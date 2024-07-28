<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class BooleanField extends NumberType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "TINYINT";
		$kwargs['vtype'] = "number";
		$kwargs['ctype'] = isset($kwargs['ctype']) ? $kwargs['ctype'] : FormControlTypes::NUMBER->value;
		$kwargs['ptype'] = "int";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length']   = 1; #The number of digits to display
		$kwargs['absolute'] = true;
		$kwargs['zero']     = true;
		$kwargs['max']      = 1;
		$kwargs['min']      = 0;

		parent::__construct(...$kwargs);
	}
}
?>