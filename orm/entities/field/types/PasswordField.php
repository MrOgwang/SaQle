<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class PasswordField extends CharField {
	 //the miminum strength
	 protected ?int $min_strength = null;

	 //the hash algorithm
	 protected ?string $hash = null;
	 
	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }

	 public function min_strength(int $min_strength){
	 	 $this->min_strength = $min_strength;
	 	 return $this;
	 }

	 public function get_min_strength(){
	 	 return $this->min_strength;
	 }

	 public function hash(string $hash){
	 	 $this->hash = $hash;
	 	 return $this;
	 }

	 public function get_hash(){
	 	 return $this->hash;
	 }
}

