<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class TinyTextField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type']    = "TINYTEXT";
		 $kwargs['length']         = isset($kwargs['length']) ? $kwargs['length'] : 255;
		 $kwargs['maximum']        = isset($kwargs['maximum']) ? $kwargs['maximum'] : 255;
		 parent::__construct(...$kwargs);
	 }
}
