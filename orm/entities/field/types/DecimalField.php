<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class DecimalField extends FloatField {
     
     //the total number of digits
     #[FieldDefinition()]
     #[ShouldValidate()]
     protected ?int $precision = null;

     //digits after decimal point
     #[FieldDefinition()]
     #[ShouldValidate()]
     protected ?int $scale = null;

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

     protected function initialize_defaults(){

         $this->native_type = "float";
         $this->type = ColumnType::DECIMAL;

         parent::initialize_defaults();

     }
}

