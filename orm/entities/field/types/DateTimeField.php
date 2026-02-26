<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class DateTimeField extends TemporalField {

     #[ShouldValidate()]
	 protected mixed $min_datetime = null;

     #[ShouldValidate()]
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
     #[FieldDefinition()]
	 protected string $storage = "unix";

     public function min_datetime(mixed $min_datetime){
         $this->min_datetime = $min_datetime;
         return $this;
     }

     public function get_min_datetime(){
         return $this->min_datetime;
     }

     public function max_datetime(mixed $max_datetime){
         $this->max_datetime = $max_datetime;
         return $this;
     }

     public function get_max_datetime(){
         return $this->max_datetime;
     }

     public function storage(string $storage){
         $this->storage = $storage;
         return $this;
     }

     public function get_storage(){
         return $this->storage;
     }

     protected function validate_field_state(){
         if($this->max_datetime && $this->min_datetime && ($this->min_datetime > $this->max_datetime)){
             $this->errors[] = "Minimum date and time cannot be more than the maximum date and time!";
         }

         parent::validate_field_state();
     }

     protected function initialize_defaults(){

         if(!$this->storage){
             $this->storage = 'unix';
         }

         if($this->storage === 'unix'){
             $this->native_type = "integer";
         }

         $this->type = ColumnType::DATETIME;

         parent::initialize_defaults();
     }
}

