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

	 public function country(string $country){
	 	 $this->country = $country;
	 	 return $this;
	 }

	 public function get_country(){
	 	 return $this->country;
	 }

	 public function format(string $format){
	 	 $this->format = $format;
	 	 return $this;
	 }

	 public function get_format(){
	 	 return $this->format;
	 }

	 public function international(bool $international = true){
	 	 $this->international = $international;
	 	 return $this;
	 }

	 public function is_international(){
	 	 return $this->international;
	 }
}

