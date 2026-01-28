<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

class NumericField extends Field {

	 //the minimum value allowed
	 protected mixed $min = null;

	 //the maximum value allowed
	 protected mixed $max = null;

	 //disallow negative numbers
	 protected bool $unsigned = false;

	 //increment step
	 protected mixed $step = null;

	 //display format
	 protected ?string $format = null;

	 //whether to auto incerement
	 protected bool $auto = false;

	 public function auto(bool $auto = true){
	 	 $this->auto = $auto;
	 	 return $this;
	 }
}

