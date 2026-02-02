<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class TimeField extends TemporalField {

	 protected mixed $min_time = null;

	 protected mixed $max_time = null;

	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = ColumnType::TIME;
	 	 parent::__construct(...$kwargs);
	 }

	 public function min_time(mixed $min_time){
         $this->min_time = $min_time;
         return $this;
     }

     public function get_min_time(){
         return $this->min_time;
     }

     public function max_time(mixed $max_time){
         $this->max_time = $max_time;
         return $this;
     }

     public function get_max_time(){
         return $this->max_time;
     }
}

