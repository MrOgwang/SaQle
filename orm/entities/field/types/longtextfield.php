<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class LongTextField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "LONGTEXT";
		 $kwargs['length']      = isset($kwargs['length']) ? $kwargs['length'] : 4294967295;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : 4294967295;
		 parent::__construct(...$kwargs);
	 }
}
