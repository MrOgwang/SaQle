<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Field;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class VirtualField extends Field {

	 protected function initialize_defaults(){

	 	 $this->virtual = true;

	 	 parent::initialize_defaults();

     }
	 
}

