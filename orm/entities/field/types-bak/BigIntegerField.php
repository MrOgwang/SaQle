<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class BigIntegerField extends NumberType implements IField{
	 public function __construct(...$kwargs){
	 	 $kwargs['column_type'] = "BIGINT";
	 	 $kwargs['length']      = 20; #The number of digits to display
	 	 $absolute              = $kwargs['absolute'] ?? false;
	 	 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : ($absolute ? 2000000001 : 1000000000 );
		 $kwargs['minimum']     = isset($kwargs['minimum']) ? $kwargs['minimum'] : ($absolute ? 0 : -1000000001 );
		 parent::__construct(...$kwargs);
	 }
	 
}
