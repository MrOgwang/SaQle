<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;

class CharField extends TextualField {
	 public function __construct(...$kwargs){
	 	 $kwargs['max_length'] = $kwargs['max_length'] ?? 100;
	 	 parent::__construct(...$kwargs);
	 }
}

