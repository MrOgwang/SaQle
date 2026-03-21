<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class JsonValidator extends IValidator {

     protected function threshold_type() : string {
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
                 "{$this->field} must be a valid JSON string."
             );
         }

         $value = trim($value);

         //validate JSON
         json_decode($value);

         if(json_last_error() !== JSON_ERROR_NONE){
             return new ValidationResult(
                 false,
                 "{$this->field} must be valid JSON. Error: ".json_last_error_msg()
             );
         }

         //passed
         return new ValidationResult(true, null);
     }
}
