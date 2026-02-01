<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Database\ColumnType;

class DecimalField extends FloatField {
     
     //the total number of digits
     protected ?int $precision = null;

     //digits after decimal point
     protected ?int $scale = null;

	 public function __construct(...$kwargs){
         $kwargs['type'] = ColumnType::DECIMAL;
	 	 parent::__construct(...$kwargs);
	 }

     public function precision(int $precision){
         $this->precision = $precision;
         return $this;
     }

     public function get_precision(){
         return $this->precision;
     }

     public function scale(int $scale){
         $this->scale = $scale;
         return $this;
     }

     public function get_scale(){
         return $this->scale;
     }
}

