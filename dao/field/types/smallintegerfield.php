<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class SmallIntegerField extends NumberType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "SMALLINT";
		$kwargs['vtype'] = "number";
		$kwargs['ctype'] = isset($kwargs['ctype']) ? $kwargs['ctype'] : FormControlTypes::NUMBER->value;
		$kwargs['ptype'] = "int";

		/**
		 * Fill in the validation props
		 * */
		$kwargs['length']   = 6; #The number of digits to display
		$kwargs['absolute'] = $kwargs['absolute'] ?? false;
		$kwargs['zero']     = $kwargs['zero'] ?? true;
		$kwargs['max']      = isset($kwargs['max']) ? $kwargs['max'] : ($kwargs['absolute'] ? 65535 : 32767);
		$kwargs['min']      = isset($kwargs['min']) ? $kwargs['min'] : $kwargs['absolute'] ? 0 : -32768;

		parent::__construct(...$kwargs);
	}
}
?>