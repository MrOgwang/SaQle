<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Field;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class VirtualField extends Field {
	
	 public function __construct(...$kwargs){
	 	 $kwargs['virtual'] = true;
	 	 parent::__construct(...$kwargs);
	 }
}

