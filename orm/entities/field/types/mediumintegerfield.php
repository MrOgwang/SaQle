<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class MediumIntegerField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "MEDIUMINT";
		 $kwargs['length']      = 9; #The number of digits to display
		 $absolute              = $kwargs['absolute'] ?? false;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : ($absolute ? 1001 : 500);
		 $kwargs['minimum']     = isset($kwargs['minimum']) ? $kwargs['minimum'] : ($absolute ? 0 : -501 );
		 parent::__construct(...$kwargs);
	 }
}
