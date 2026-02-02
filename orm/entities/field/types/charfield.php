<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class CharField extends TextualField {
	 public function __construct(...$kwargs){
	 	 $kwargs['max_length'] = $kwargs['max_length'] ?? 100;
	 	 $kwargs['type'] = $kwargs['type'] ?? ColumnType::CHAR;
	 	 parent::__construct(...$kwargs);
	 }
}

