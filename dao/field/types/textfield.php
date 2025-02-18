<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class TextField extends TextType implements IField{
	 public function __construct(...$kwargs){
	 	 $kwargs['column_type'] = "TEXT";
		 $kwargs['length']      = isset($kwargs['length']) ? $kwargs['length'] : 65535;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : 65535;
		 parent::__construct(...$kwargs);
	 }
}
?>