<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class WhitelistValidator extends IValidator {

     protected function threshold_type() : string {
         return 'array';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //threshold must be non-empty array
         if(!is_array($this->threshold) || empty($this->threshold)){
             return new ValidationResult(
                 false,
                 "whitelist rule for {$this->field} must be a non-empty array."
             );
         }

         //value must be string
         if(!is_string($value) || trim($value) === ''){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid email."
             );
         }

         $value = strtolower(trim($value));

         //validate email structure first
         if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid email address."
             );
         }

         //normalize whitelist
         $whitelist = array_map(
             fn($item) => strtolower(trim((string)$item)),
             $this->threshold
         );

         //check whitelist
         $domain = substr(strrchr($value, "@"), 1); // domain only

         foreach($whitelist as $allowed){
             if(str_starts_with($allowed, '@')){
                 //Domain match
                 if($domain === ltrim($allowed, '@')){
                     return new ValidationResult(true, null);
                 }
             }else{
                 //Exact email match
                 if($value === $allowed){
                     return new ValidationResult(true, null);
                 }
             }
         }

         return new ValidationResult(
             false,
             "{$this->field} is not in the allowed whitelist."
         );
     }
}
