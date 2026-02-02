<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class SlugField extends CharField {
	
	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

