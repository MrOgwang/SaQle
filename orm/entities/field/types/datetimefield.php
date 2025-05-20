<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class DateTimeField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "DATETIME";
		 $kwargs['length']      = 30;
		 $kwargs['maximum']     = 30;
		 parent::__construct(...$kwargs);
	 }
}
