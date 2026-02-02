<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class UuidField extends CharField {
	 
	 public function __construct(...$kwargs){
	 	 $kwargs['length'] = 36;
	 	 parent::__construct(...$kwargs);
	 }
}

