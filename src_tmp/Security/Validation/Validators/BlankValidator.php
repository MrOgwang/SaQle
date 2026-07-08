<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class BlankValidator extends IValidator {

     protected function threshold_type(): string { 
         return 'bool';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         //only text allowed (null included for evaluation)
         if(!is_string($value) && $value !== null){
             return new ValidationResult(false, "{$this->field} must be a string.");
         }

         //Determine blank state
         $is_blank = $value === null || trim($value) === '';

         //blank = true → empty allowed → always valid
         if($this->threshold === true){
             return new ValidationResult(true, null);
         }

         //blank = false → empty NOT allowed
         return !$is_blank ? new ValidationResult(true, null) : new ValidationResult(false, "{$this->field} cannot be blank.");
    }
}
