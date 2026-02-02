<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class TextField extends TextualField {
	 
	 //the minimum length allowed
	 #[FieldDefinition()]
	 protected string $size = 'regular'; //big, small, medium, tiny, regular

	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = ColumnType::TEXT;
	 	 parent::__construct(...$kwargs);
	 }
}

