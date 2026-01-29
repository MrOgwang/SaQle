<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;

class DateTimeField extends TemporalField {

	 protected mixed $min_datetime = null;

	 protected mixed $max_datetime = null;

     /**
      * how datetime value persisted and transported, independent of the database engine
      * 
      * Options:
      * 
      * timestamp - A database-level datetime representation
      * 1so       - A textual representation of date + time: e.g 2026-01-28T14:45:00Z
      * unix      - An integer representing seconds (or milliseconds)
      * */
	 protected string $storage = "unix";

	 public function __construct(...$kwargs){
	 	 parent::__construct(...$kwargs);
	 }
}

