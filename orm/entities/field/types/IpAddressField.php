<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class IpAddressField extends CharField {
	 protected string $version = 'IPv4';

	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

