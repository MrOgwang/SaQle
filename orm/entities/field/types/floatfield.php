<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class FloatField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type']   = "FLOAT";
		 $kwargs['primitive_type'] = "float";
		 parent::__construct(...$kwargs);
	 }
}
?>