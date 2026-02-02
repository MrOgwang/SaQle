<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\NumericField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class FloatField extends NumericField {
	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = $kwargs['type'] ?? ColumnType::FLOAT;
	 	 parent::__construct(...$kwargs);
	 }
}

