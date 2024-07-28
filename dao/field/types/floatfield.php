<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class FloatField extends NumberType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "FLOAT";
		$kwargs['vtype'] = "number";
		$kwargs['ctype'] = isset($kwargs['ctype']) ? $kwargs['ctype'] : FormControlTypes::NUMBER->value;
		$kwargs['ptype'] = "float";

		/**
		 * Fill in the validation props
		 * */
		//$kwargs['length']   = null; #The number of digits to display
		$kwargs['absolute'] = $kwargs['absolute'] ?? false;
		$kwargs['zero']     = $kwargs['zero'] ?? true;

		parent::__construct(...$kwargs);
	}
}
?>