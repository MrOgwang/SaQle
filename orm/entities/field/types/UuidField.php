<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class UuidField extends CharField {

	 protected function initialize_defaults(){
         $this->length = 36;

         parent::initialize_defaults();
     }

}

