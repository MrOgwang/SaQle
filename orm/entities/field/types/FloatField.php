<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\NumericField;

class FloatField extends NumericField {
	 public function __construct(...$kwargs){
	 	$kwargs['type'] = "float";
	 	parent::__construct(...$kwargs);
	 }
}

