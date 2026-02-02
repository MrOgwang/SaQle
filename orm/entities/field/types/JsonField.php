<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class JsonField extends TextualField {
	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = ColumnType::JSON;
	 	 parent::__construct(...$kwargs);
	 }
}

