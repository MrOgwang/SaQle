<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class ScaleValidator extends IValidator {
    
     protected function threshold_type() : string {
         return 'int';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //threshold must be integer >= 0
         if(!is_int($this->threshold) || $this->threshold < 0){
             return new ValidationResult(
                 false,
                 "scale rule for {$this->field} must be a non-negative integer."
             );
         }

         //value must be numeric
         if(!is_numeric($value)){
             return new ValidationResult(
                 false,
                 "{$this->field} must be numeric."
             );
         }

         $value_str = (string)$value;

         //count digits after decimal
         $parts = explode('.', $value_str);
         $scale_digits = isset($parts[1]) ? strlen(rtrim($parts[1], '0')) : 0;

         if($scale_digits > $this->threshold){
             return new ValidationResult(
                 false,
                 "{$this->field} must have at most {$this->threshold} digits after the decimal point."
             );
         }

         return new ValidationResult(true, null);
     }
}
