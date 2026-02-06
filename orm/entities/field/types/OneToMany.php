<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class OneToMany extends RelationField {
	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = ColumnType::CHAR;
	 	 $kwargs['many'] = true;
	 	 parent::__construct(...$kwargs);
	 }
}
