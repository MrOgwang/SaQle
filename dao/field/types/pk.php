<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\Attributes\PrimaryKey;
use SaQle\Dao\Field\FormControlTypes;

class Pk extends Scalar implements IField{
	private $type;
	public function __construct(string $type, ...$kwargs){
		$this->type = PRIMARY_KEY_TYPE;
		$this->attributes[PrimaryKey::class] = ['type' => $type];
		if($this->type == "GUID"){
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
			$kwargs['length'] = 255;
			$kwargs['max'] = 255;
		}else{
			 /**
			 * Fill in the data types.
			 * */
			$kwargs['dtype'] = "INT";
			$kwargs['vtype'] = "number";
			$kwargs['ctype'] = isset($kwargs['ctype']) ? $kwargs['ctype'] : FormControlTypes::NUMBER->value;
			$kwargs['ptype'] = "int";

			/**
			 * Fill in the validation props
			 * */
			$kwargs['length']   = 11;
			$kwargs['absolute'] = true;
			$kwargs['zero']     = false;
			$kwargs['max']      = 4294967295;
			$kwargs['min']      = 1;
		}
		$kwargs['required'] = true;

		parent::__construct(...$kwargs);
	}

	public function get_key_type(){
		return $this->type;
	}
}
?>