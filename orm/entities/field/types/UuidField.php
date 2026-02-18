<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class UuidField extends CharField {

     #[ShouldValidate()]
     protected bool $uuid = true;

	 protected function initialize_defaults(){
         $this->length = 36;

         parent::initialize_defaults();
     }

}

