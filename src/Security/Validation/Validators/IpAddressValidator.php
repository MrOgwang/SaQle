<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class IpAddressValidator extends IValidator {

     protected function threshold_type(): string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //disabled → always pass
         if($this->threshold === false){
             return new ValidationResult(true, null);
         }

         //value must be string
         if(!is_string($value) || trim($value) === '') {
             return new ValidationResult(
                 false,
                "{$this->field} must be a valid IP address."
             );
         }

         $value = trim($value);

         //validate IP
         if(!filter_var($value, FILTER_VALIDATE_IP)){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid IP address."
             );
         }

         return new ValidationResult(true, null);
     }
}
