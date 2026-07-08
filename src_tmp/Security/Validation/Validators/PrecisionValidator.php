<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class PrecisionValidator extends IValidator {

     protected function threshold_type() : string {
         return 'int';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //value must be numeric
         if(!is_numeric($value)){
             return new ValidationResult(
                 false,
                 "{$this->field} must be numeric."
             );
         }

         $value_str = preg_replace('/[^0-9]/', '', (string)$value); // remove decimal & sign

         $digits = strlen($value_str);

         if($digits > $threshold){
             return new ValidationResult(
                 false,
                 "{$this->field} must have at most {$this->threshold} total digits."
             );
         }

         return new ValidationResult(true, null);
     }
}
