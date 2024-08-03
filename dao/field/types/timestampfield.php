<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\FormControlTypes;

class TimestampField extends TextType implements IField{
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "DATETIME";
		$kwargs['vtype'] = "text";
		$kwargs['ctype'] = FormControlTypes::DATETIMELOCAL->value;
		$kwargs['ptype'] = "string";
		$kwargs['db_auto_init'] = $kwargs['db_auto_init'] ?? DB_AUTO_INIT_TIMESTAMP;
		$kwargs['db_auto_update'] = $kwargs['db_auto_update'] ?? DB_AUTO_UPDATE_TIMESTAMP;


		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = 30;
		$kwargs['max']    = 30;

		parent::__construct(...$kwargs);
	}
}
?>