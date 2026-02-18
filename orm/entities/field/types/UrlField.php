<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class UrlField extends CharField {
	 
	 //Allowed schemes (http, https)
	 #[ShouldValidate()]
	 protected ?array $schemes = [];

	 //Attempt URL validation
	 protected bool $verify = false;

	 //Require top-level domain
	 #[ShouldValidate()]
	 protected bool $require_tld = false;
	 
}

