<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Core\Support\CharSet;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class TextualField extends Field {
	 //the minimum length allowed
	 #[ShouldValidate()]
	 protected ?int $min_length = null;

	 //the maximum length allowed
	 #[ShouldValidate()]
	 #[FieldDefinition()]
	 protected ?int $max_length = null;

	 //the exact length allowed
	 #[ShouldValidate()]
	 protected ?int $length = null;

	 //the regex pattern to match
	 #[ShouldValidate()]
	 protected ?string $pattern = null;

	 //the field character set
	 #[ShouldValidate()]
	 protected ?CharSet $charset = null;

	 //whether to allow blank or not
	 #[ShouldValidate()]
	 protected bool $blank = true;

	 public function length(int $length){
	 	 $this->length = $length;
	 	 return $this;
	 }

	 public function get_length(){
	 	 return $this->length;
	 }

	 public function max_length(int $max_length){
	 	 $this->max_length = $max_length;
	 	 return $this;
	 }

	 public function get_max_length(){
	 	 return $this->max_length;
	 }

	 public function min_length(int $min_length){
	 	 $this->min_length = $min_length;
	 	 return $this;
	 }

	 public function get_min_length(){
	 	 return $this->min_length;
	 }

	 public function pattern(string $pattern){
	 	 $this->pattern = $pattern;
	 	 return $this;
	 }

	 public function get_pattern(){
	 	 return $this->pattern;
	 }

	 public function charset(CharSet $charset){
	 	 $this->charset = $charset;
	 	 return $this;
	 }

	 public function get_charset(){
	 	 return $this->charset;
	 }

	 public function blank(bool $blank = true){
	 	 $this->blank = $blank;
	 	 return $this;
	 }

	 public function is_blank(){
	 	 return $this->blank;
	 }

	 protected function validate_field_state(){
	 	 if($this->length && $this->max_length){
	 	 	 $this->errors[] = "Having length and maximum length at the same time is ambigous!";
	 	 }

	 	 if($this->length && $this->min_length){
	 	 	 $this->errors[] = "Having length and minimum length at the same time is ambigous!";
	 	 }

	 	 if($this->max_length && $this->min_length){
	 	 	 if($this->min_length > $this->max_length){
	 	 	 	 $this->errors[] = "Minimum length cannot be more than the maximum length!";
	 	 	 }
     	 }

     	 if($this->required && $this->blank){
	 	 	 $this->errors[] = "A required field cannot be blank!";
     	 }

     	 parent::validate_field_state();
	 }

	 protected function initialize_defaults(){
	 	 if($this->length){
	 	 	 $this->max_length = $this->length;
	 	 	 $this->min_length = $this->length;
	 	 }

	 	 parent::initialize_defaults();
     }

     protected function initialize_defaults(){
     	 
     	 if(!$this->native_type){
     	 	 $this->native_type = "string";
     	 }

     	 parent::initialize_defaults();
     	 
     }
}

