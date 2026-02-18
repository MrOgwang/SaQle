<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class TimeField extends TemporalField {

     #[ShouldValidate()]
	 protected mixed $min_time = null;

     #[ShouldValidate()]
	 protected mixed $max_time = null;

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

     protected function validate_field_state(){
         if($this->max_time && $this->min_time){
             if($this->min_time > $this->max_time){
                 $this->errors[] = "Minimum time cannot be more than the maximum time!";
             }
         }

         parent::validate_field_state();
     }

     protected function initialize_defaults(){

         $this->type = ColumnType::TIME;

         parent::initialize_defaults();

     }
}

