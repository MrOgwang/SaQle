<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class IntegerField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "INT";
		 $kwargs['length']      = 11; #The number of digits to display
		 $absolute              = $kwargs['absolute'] ?? false;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : ($absolute ? 10001 : 5000 );
		 $kwargs['minimum']     = isset($kwargs['minimum']) ? $kwargs['minimum'] : ($absolute ? 0 : -5001 );
		 parent::__construct(...$kwargs);
	 }
}
