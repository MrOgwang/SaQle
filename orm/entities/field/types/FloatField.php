<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\NumericField;
use SaQle\Orm\Database\ColumnType;

class FloatField extends NumericField {
	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = $kwargs['type'] ?? ColumnType::FLOAT;
	 	 parent::__construct(...$kwargs);
	 }
}

