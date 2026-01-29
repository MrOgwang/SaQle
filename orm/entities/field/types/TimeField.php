<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;

class TimeField extends TemporalField {

	 protected mixed $min_time = null;

	 protected mixed $max_time = null;

	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

