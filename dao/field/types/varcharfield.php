<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class VarCharField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type']    = "VARCHAR";
		 $kwargs['length']         = isset($kwargs['length']) ? $kwargs['length'] : 255;
		 $kwargs['maximum']        = isset($kwargs['maximum']) ? $kwargs['maximum'] : $kwargs['length'];
		 parent::__construct(...$kwargs);
	 }
}
?>