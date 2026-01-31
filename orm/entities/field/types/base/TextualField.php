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

	 public function __construct(...$kwargs){
	 	$kwargs['type'] = "string";
	 	parent::__construct(...$kwargs);
	 }

	 public function length(int $length){
	 	 $this->length = $length;
	 	 return $this;
	 }

	 public function get_length(){
	 	 return $this->length;
	 }
}

