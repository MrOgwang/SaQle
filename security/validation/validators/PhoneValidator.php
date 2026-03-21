<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class PhoneValidator extends IValidator {

     protected function threshold_type() : string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {
         //disabled → always pass
         if($this->threshold === false){
             return new ValidationResult(true, null);
         }

         //value must be string
         if(!is_string($value) || trim($value) === ''){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid phone number."
            );
         }

         $value = trim($value);

         //basic phone number regex (digits, optional +, spaces, -, ())
         //Minimum 6 digits, maximum 15 digits
         $digits = preg_replace('/\D+/', '', $value); //remove non-digits

         if(strlen($digits) < 6 || strlen($digits) > 15) {
             return new ValidationResult(
                 false,
                 "{$this->field} must contain between 6 and 15 digits."
             );
         }

         //Optional: Check allowed characters
         if(!preg_match('/^\+?[0-9\s\-\(\)]+$/', $value)){
             return new ValidationResult(
                 false,
                 "{$this->field} contains invalid characters."
             );
         }

         return new ValidationResult(true, null);
     }
}
