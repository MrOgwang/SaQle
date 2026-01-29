<?php

namespace SaQle\Orm\Entities\Field\Types;

class PhoneField extends CharField {
	 
	 //the country code
	 protected ?string $country = null;

	 //the expected phone format
	 protected ?string $format = null;

	 //provide phone in internationally recognizable format
	 protected bool $international = false;

	 public function __construct(...$kwargs){
	 	parent::__construct(...$kwargs);
	 }
}

