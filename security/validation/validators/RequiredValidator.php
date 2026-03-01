<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class RequiredValidator extends IValidator {

     protected function threshold_type(): string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {
         //if required is false, always pass
         if($this->threshold === false){
             return new ValidationResult(true, null);
         }

         //Determine presence
         if(is_null($value)){
             return new ValidationResult(false, "{$this->field} is required.");
         }

         return new ValidationResult(true, null);
     }
}
