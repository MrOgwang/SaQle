<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class DateField extends TextType implements IField{
	 public function __construct(...$kwargs){
	 	 $kwargs['column_type'] = "DATE";
		 $kwargs['length']      = 10;
		 $kwargs['maximum']     = 10;
		 parent::__construct(...$kwargs);
	 }
}
?>