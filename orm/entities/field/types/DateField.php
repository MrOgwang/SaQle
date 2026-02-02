<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class DateField extends TemporalField {

	 protected mixed $min_date = null;

	 protected mixed $max_date = null;

	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = ColumnType::DATE;
	 	 parent::__construct(...$kwargs);
	 }

	 public function min_date(mixed $min_date){
         $this->min_date = $min_date;
         return $this;
     }

     public function get_min_date(){
         return $this->min_date;
     }

     public function max_date(mixed $max_date){
         $this->max_date = $max_date;
         return $this;
     }

     public function get_max_date(){
         return $this->max_date;
     }
}

