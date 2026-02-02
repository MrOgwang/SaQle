<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TemporalField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

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
     #[FieldDefinition()]
	 protected string $storage = "unix";

	 public function __construct(...$kwargs){
         $kwargs['type'] = ColumnType::DATETIME;
	 	 parent::__construct(...$kwargs);
	 }

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
}

