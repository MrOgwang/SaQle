<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class DoubleField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type']    = "DOUBLE";
		 $kwargs['primitive_type'] = "float";
		 $kwargs['length']         = null; #The number of digits to display
		 parent::__construct(...$kwargs);
	 }
}
