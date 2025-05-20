<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class TimeField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "TIME";
		 $kwargs['length']      = 50;
		 $kwargs['maximum']     = 50;
		 parent::__construct(...$kwargs);
	 }
}
