<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class TemporalField extends Field {
	 //the format 
	 #[ShouldValidate()]
	 protected ?string $format = null;

	 //set to current date on save
	 #[FieldDefinition()]
	 protected bool $auto_now = false;

	 //set to current date on creation
	 #[FieldDefinition()]
	 protected bool $auto_now_add = false;

	 //Whether to store with timezone
	 #[ShouldValidate()]
	 protected bool $timezone = false;

	 public function format(string $format){
	 	 $this->format = $format;
	 	 return $this;
	 }

	 public function auto_now(bool $auto_now = true){
	 	 $this->auto_now = $auto_now;
	 	 return $this;
	 }

	 public function auto_now_add(bool $auto_now_add = true){
	 	 $this->auto_now_add = $auto_now_add;
	 	 return $this;
	 }

	 public function timezone(bool $timezone = true){
	 	 $this->timezone = $timezone;
	 	 return $this;
	 }


	 public function get_format(){
	 	 return $this->format;
	 }

	 public function get_auto_now(){
	 	 return $this->auto_now;
	 }

	 public function get_auto_now_add(){
	 	 return $this->auto_now_add;
	 }

	 public function get_timezone(){
	 	 return $this->timezone;
	 }

	 protected function initialize_defaults(){
  
     	 if(!$this->native_type){
     	 	 $this->native_type = "string";
     	 }

     	 parent::initialize_defaults();
     }
}

