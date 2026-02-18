<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class JsonField extends TextualField {

	 protected function initialize_defaults(){

         $this->type = ColumnType::JSON;

         parent::initialize_defaults();

     }
}

