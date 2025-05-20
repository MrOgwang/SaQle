<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class CharField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "CHAR";
		 $kwargs['length']      = 1;
		 $kwargs['maximum']     = 1;
		 parent::__construct(...$kwargs);
	 }
}
