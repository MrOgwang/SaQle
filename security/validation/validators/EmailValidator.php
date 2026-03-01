<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class EmailValidator extends IValidator {
     protected function threshold_type(): string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {
         //If disabled → always pass
         if($this->threshold === false) {
             return new ValidationResult(true, null);
         }

         //must be string
         if(!is_string($value) || trim($value) === ''){
             return new ValidationResult(false, "{$this->field} must be a valid email address.");
         }

         $value = trim($value);

         //Validate email
         if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
             return new ValidationResult(false, "{$field} must be a valid email address.");
         }

         //Passed
         return new ValidationResult(true, null);
     }
}
