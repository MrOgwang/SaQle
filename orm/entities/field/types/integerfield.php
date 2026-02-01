<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\NumericField;
use SaQle\Orm\Database\ColumnType;

class IntegerField extends NumericField {
	 //the minimum length allowed
	 protected string $size = 'regular'; //big, small, medium, tiny, regular

	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = ColumnType::INTEGER;
	 	 parent::__construct(...$kwargs);
	 }
}

