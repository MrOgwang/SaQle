<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class IpAddressField extends CharField {
	 
	 #[ShouldValidate()]
	 protected bool $ip = true;

	 protected string $version = 'IPv4';
	 
}

