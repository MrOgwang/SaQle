<?php

namespace SaQle\Orm\Entities\Field\Types;

class DecimalField extends FloatField {
     
     //the total number of digits
     protected ?int $precision = null;

     //digits after decimal point
     protected ?int $scale = null;

	 public function __construct(...$kwargs){
	 	parent::__construct(...$kwargs);
	 }
}

