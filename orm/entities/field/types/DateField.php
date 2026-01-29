<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;

class DateField extends TemporalField {

	 protected mixed $min_date = null;

	 protected mixed $max_date = null;

	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

