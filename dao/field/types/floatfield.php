<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class FloatField extends NumberType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type']   = "FLOAT";
		 $kwargs['primitive_type'] = "float";
		 parent::__construct(...$kwargs);
	 }
}
?>