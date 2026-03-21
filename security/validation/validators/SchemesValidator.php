<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class SchemesValidator extends IValidator {
    
     protected function threshold_type() : string {
         return 'array';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //threshold must be a non-empty array
         if(!is_array($this->threshold) || empty($this->threshold)){
             return new ValidationResult(
                 false,
                 "Schemes rule for {$field} must be a non-empty array."
             );
         }

         //normalize allowed schemes to lowercase
         $allowed_schemes = array_map(
             fn($scheme) => strtolower(trim((string)$scheme)),
             $this->threshold
         );

         //value must be string
         if(!is_string($value) || trim($value) === ''){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid URL."
             );
         }

         $value = trim($value);

         //validate URL structure
         if(!filter_var($value, FILTER_VALIDATE_URL)){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid URL."
             );
         }

         //Extract scheme safely
         $scheme = parse_url($value, PHP_URL_SCHEME);

         if($scheme === null){
             return new ValidationResult(
                 false,
                 "{$this->field} must contain a URL scheme."
             );
         }

         $scheme = strtolower($scheme);

         //validate scheme membership
         if(!in_array($scheme, $allowed_schemes, true)){
             return new ValidationResult(
                 false,
                 "{$this->field} must use one of the following schemes: " .
                 implode(', ', $allowed_schemes) . "."
             );
         }

         return new ValidationResult(true, null);
     }
}
