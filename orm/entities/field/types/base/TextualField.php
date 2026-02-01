<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Core\Support\CharSet;

class TextualField extends Field {
	//the minimum length allowed
	 protected ?int $min_length = null;

	 //the maximum length allowed
	 protected ?int $max_length = null;

	 //the exact length allowed
	 protected ?int $length = null;

	 //the regex pattern to match
	 protected ?string $pattern = null;

	 //the field character set
	 protected ?CharSet $charset = null;

	 //whether to allow blank or not
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
}

