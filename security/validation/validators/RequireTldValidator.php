<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class RequireTldValidator extends IValidator {

     protected function threshold_type() : string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {
         //if rule is false → always pass
         if($this->threshold === false){
             return new ValidationResult(true, null);
         }

         //must be non-empty string
         if(!is_string($value) || trim($value) === ''){
             return new ValidationResult(false, "{$this->field} must be a valid URL.");
         }

         $value = trim($value);

         //validate URL structure
         if(!filter_var($value, FILTER_VALIDATE_URL)){
             return new ValidationResult(false, "{$this->field} must be a valid URL.");
         }

         //extract host
         $host = parse_url($value, PHP_URL_HOST);

         if($host === null || $host === ''){
             return new ValidationResult(false, "{$this->field} must contain a valid host.");
         }

         //reject IP addresses (IPs do not have TLDs)
         if(filter_var($host, FILTER_VALIDATE_IP)){
             return new ValidationResult(false, "{$this->field} must contain a top level domain.");
         }

         //check TLD existence
         //host must contain at least one dot
         if(!str_contains($host, '.')){
             return new ValidationResult(false, "{$this->field} must contain a top level domain.");
         }

         //extract last label (TLD)
         $parts = explode('.', $host);
         $tld = end($parts);

         //validate TLD format (letters only, 2–63 chars per RFC)
         if(!preg_match('/^[a-z]{2,63}$/i', $tld)) {
             return new ValidationResult(false, "{$this->field} must contain a valid top level domain.");
         }

         return new ValidationResult(true, null);
     }
}
