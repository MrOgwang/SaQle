<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;

class IpAddressField extends CharField {
	 protected string $version = 'IPv4';

	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

