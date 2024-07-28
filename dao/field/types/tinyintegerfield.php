<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class TinyIntegerField extends NumberType implements IField{
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
		$kwargs['length']   = 4; #The number of digits to display
		$kwargs['absolute'] = $kwargs['absolute'] ?? false;
		$kwargs['zero']     = $kwargs['zero'] ?? true;
		$kwargs['max']      = isset($kwargs['max']) ? $kwargs['max'] : ($kwargs['absolute'] ? 255 : 127);
		$kwargs['min']      = isset($kwargs['min']) ? $kwargs['min'] : ($kwargs['absolute'] ? 0 : -128);

		parent::__construct(...$kwargs);
	}
}
?>