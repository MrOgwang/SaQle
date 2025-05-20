<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class IntegerField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "INT";
		 $kwargs['length']      = 11; #The number of digits to display
		 $absolute              = $kwargs['absolute'] ?? false;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : ($absolute ? 4294967295 : 2147483647);
		 $kwargs['minimum']     = isset($kwargs['minimum']) ? $kwargs['minimum'] : ($absolute ? 0 : -2147483648);
		 parent::__construct(...$kwargs);
	 }
}
