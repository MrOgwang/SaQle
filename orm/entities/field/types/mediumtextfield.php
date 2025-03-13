<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class MediumTextField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "MEDIUMTEXT";
		 $kwargs['length']      = isset($kwargs['length']) ? $kwargs['length'] : 16777215;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : 16777215;
		 parent::__construct(...$kwargs);
	 }
}
?>