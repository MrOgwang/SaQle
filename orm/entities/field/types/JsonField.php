<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;

class JsonField extends TextualField {
	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = ColumnType::JSON;
	 	 parent::__construct(...$kwargs);
	 }
}

