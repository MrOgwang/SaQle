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

