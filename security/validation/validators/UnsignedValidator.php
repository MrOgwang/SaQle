<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class UnsignedValidator extends IValidator {
     protected function threshold_type(): string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         //value must be numeric (int or float)
         if(!is_int($value) && !is_float($value)){
             return new ValidationResult(false, "{$this->field} must be a numeric value.");
         }

         //if unsigned = false → allow everything
         if($this->threshold === false) {
             return new ValidationResult(true, null);
         }

         //unsigned = true → disallow negatives
         if($value < 0){
             return new ValidationResult(false, "{$this->field} must be an unsigned number.");
         }

         return new ValidationResult(true, null);
     }
}
