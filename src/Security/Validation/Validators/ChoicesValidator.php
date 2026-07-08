<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class ChoicesValidator extends IValidator {
     protected function threshold_type(): string {
         return 'array';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {
         if(!in_array($value, $this->threshold, true)){
             return new ValidationResult(
                 false, 
                 "{$this->field} must be one of the following values: ".implode(', ', $this->threshold)
             );
         }

         return new ValidationResult(true, null);
     }
}
