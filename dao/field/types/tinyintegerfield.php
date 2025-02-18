<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class TinyIntegerField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "TINYINT";
		 $kwargs['length']      = 4; #The number of digits to display
		 $absolute              = $kwargs['absolute'] ?? false;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : ($absolute ? 255 : 127);
		 $kwargs['minimum']     = isset($kwargs['minimum']) ? $kwargs['minimum'] : ($absolute ? 0 : -128);
		 parent::__construct(...$kwargs);
	 }
}
?>