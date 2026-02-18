<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class EmailField extends CharField {
	 
	 //the domain white list
	 #[ShouldValidate()]
	 protected ?array $whitelist = null;

	 //the domain black list
	 #[ShouldValidate()]
	 protected ?array $blacklist = null;

	 //very email exists
	 #[ShouldValidate()]
	 protected bool $dnscheck = false;
	 
	 public function whitelist(array $whitelist){
	 	 $this->whitelist = $whitelist;
	 	 return $this;
	 }

	 public function get_whitelist(){
	 	 return $this->whitelist;
	 }

	 public function blacklist(array $blacklist){
	 	 $this->blacklist = $blacklist;
	 	 return $this;
	 }

	 public function get_blacklist(){
	 	 return $this->blacklist;
	 }

	 public function dnscheck(bool $dnscheck = true){
	 	 $this->dnscheck = $dnscheck;
	 	 return $this;
	 }

	 public function get_dnscheck(){
	 	 return $this->dnscheck;
	 }
}

