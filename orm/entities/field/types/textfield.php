<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class TextField extends TextualField {
	 
	 //the minimum length allowed
	 #[FieldDefinition()]
	 protected string $size = 'regular'; //big, small, medium, tiny, regular

	 protected function initialize_defaults(){

         $this->type = ColumnType::TEXT;

         parent::initialize_defaults();

     }
}

