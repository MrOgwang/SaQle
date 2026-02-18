<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\NumericField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class IntegerField extends NumericField {
	
	 //the minimum length allowed
	 #[FieldDefinition()]
	 protected string $size = 'regular'; //big, small, medium, tiny, regular

	 protected function initialize_defaults(){

         $this->type = ColumnType::INTEGER;
         $this->native_type = "integer";

         parent::initialize_defaults();

     }
}

