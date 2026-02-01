<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Field;

class VirtualField extends Field {
	
	 public function __construct(...$kwargs){
	 	 $kwargs['virtual'] = true;
	 	 parent::__construct(...$kwargs);
	 }
}

