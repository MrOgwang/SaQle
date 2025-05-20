<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class BooleanField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "TINYINT";
		 $kwargs['length']      = 1; #The number of digits to display
		 $kwargs['absolute']    = true;
		 $kwargs['zero']        = true;
		 $kwargs['maximum']     = 1;
		 $kwargs['minimum']     = 0;
		 parent::__construct(...$kwargs);
	 }
}
