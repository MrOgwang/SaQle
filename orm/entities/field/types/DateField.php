<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class DateField extends TemporalField {

     #[ShouldValidate()]
	 protected mixed $min_date = null;

     #[ShouldValidate()]
	 protected mixed $max_date = null;

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

     protected function validate_field_state(){
         if($this->max_date && $this->min_date && ($this->min_date > $this->max_date)){
             $this->errors[] = "Minimum date cannot be more than the maximum date!";
         }

         parent::validate_field_state();
     }

     protected function initialize_defaults(){
         $this->type = ColumnType::DATE;

         parent::initialize_defaults();
     }
}

