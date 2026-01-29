<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Core\Support\CharSet;

class TemporalField extends Field {
	 //the format 
	 protected ?string $format = null;

	 //set to current date on save
	 protected bool $auto_now = false;

	 //set to current date on creation
	 protected bool $auto_now_add = false;

	 //Whether to store with timezone
	 protected bool $timezone = false;

	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

