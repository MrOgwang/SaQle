<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class SlugValidator extends IValidator {

     protected function threshold_type(): string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //If slug rule disabled → always pass
         if($this->threshold === false){
             return new ValidationResult(true, null);
         }

         //value must be string
         if(!is_string($value)){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid slug."
             );
         }

         $value = trim($value);

         //empty string fails (slug must contain content)
         if($value === ''){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid slug."
             );
         }

         //strict slug regex
         $pattern = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

         if(!preg_match($pattern, $value)){
             return new ValidationResult(
                 false,
                 "{$this->field} must contain only lowercase letters, numbers, and single hyphens between words."
             );
         }

         return new ValidationResult(true, null);
     }
}
