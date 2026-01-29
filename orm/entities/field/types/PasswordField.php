<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;

class PasswordField extends CharField {
	 //the miminum strength
	 protected ?int $min_strength = null;

	 //the hash algorithm
	 protected ?string $hash = null;
	 
	 public function __construct(...$kwargs){
	 	parent::__construct(...$kwargs);
	 }
}

