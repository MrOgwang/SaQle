<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class BigIntegerField extends NumberType implements IField{
	 public function __construct(...$kwargs){
	 	 $kwargs['column_type'] = "BIGINT";
	 	 $kwargs['length']      = 20; #The number of digits to display
	 	 $absolute              = $kwargs['absolute'] ?? false;
	 	 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : ($absolute ? 18446744073709551616 : 99223372036854775808);
		 $kwargs['minimum']     = isset($kwargs['minimum']) ? $kwargs['minimum'] : ($absolute ? 0 : -72036854775808);
		 parent::__construct(...$kwargs);
	 }
}
?>