<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class JsonField extends TextualField {

     #[ShouldValidate()]
     protected bool $json = true;

	 protected function initialize_defaults(){

         $this->type = ColumnType::JSON;

         parent::initialize_defaults();

     }
}

