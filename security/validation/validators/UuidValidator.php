<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class UuidValidator extends IValidator {
     protected function threshold_type(): string { 
         return 'bool';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         //disabled → always pass
         if($this->threshold === false){
             return new ValidationResult(true, null);
         }

         //value must be string
         if(!is_string($value) || trim($value) === ''){
             return new ValidationResult(false, "{$this->field} must be a valid UUID.");
         }

         $value = trim($value);

         //regex for UUID v1–v5
         if(!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)){
             return new ValidationResult(false, "{$this->field} must be a valid UUID (v1–v5).");
         }

         return new ValidationResult(true, null);
     }
}
