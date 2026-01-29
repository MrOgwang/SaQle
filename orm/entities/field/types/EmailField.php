<?php

namespace SaQle\Orm\Entities\Field\Types;

class EmailField extends CharField {
	 
	 //the domain white list
	 protected ?array $whitelist = null;

	 //the domain black list
	 protected ?array $blacklist = null;

	 //very email exists
	 protected bool $dnscheck = false;

	 public function __construct(...$kwargs){
	 	parent::__construct(...$kwargs);
	 }
}

