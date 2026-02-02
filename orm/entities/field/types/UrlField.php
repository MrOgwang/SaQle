<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class UrlField extends CharField {
	 
	 //Allowed schemes (http, https)
	 protected ?array $schemes = [];

	 //Attempt URL validation
	 protected bool $verify = false;

	 //Require top-level domain
	 protected bool $require_tld = false;

	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

