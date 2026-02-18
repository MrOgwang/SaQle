<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class PhoneField extends CharField {
	 
	 #[ShouldValidate()]
	 protected bool $phone = true;

	 //the country code
	 #[ShouldValidate()]
	 protected ?array $countries = null;

	 //the expected phone format
	 #[ShouldValidate()]
	 protected ?string $format = null;

	 //provide phone in internationally recognizable format
	 #[ShouldValidate()]
	 protected bool $international = false;

	 public function countries(array $countries){
	 	 $this->countries = $countries;
	 	 return $this;
	 }

	 public function get_countries(){
	 	 return $this->countries;
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

