<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\NumericField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class FloatField extends NumericField {

	 protected function initialize_defaults(){

         $this->native_type = "float";
         $this->type = ColumnType::FLOAT;

         parent::initialize_defaults();

     }
}

