<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class BooleanField extends IntegerField {
	 public function __construct(...$kwargs){
		 $kwargs['unsigned'] = true;
		 $kwargs['max']      = 1;
		 $kwargs['min']      = 0;
		 $kwargs['type']     = ColumnType::BOOLEAN;
	 	 parent::__construct(...$kwargs);
	 }
}

