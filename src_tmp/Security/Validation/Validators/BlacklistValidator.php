<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class BlacklistValidator extends IValidator {

     protected function threshold_type() : string {
         return 'array';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //threshold must be non-empty array
         if(!is_array($this->threshold) || empty($this->threshold)){
             return new ValidationResult(
                 false,
                 "blacklist rule for {$this->field} must be a non-empty array."
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

         //normalize blacklist
         $blacklist = array_map(
             fn($item) => strtolower(trim((string)$item)),
             $this->threshold
         );

         $domain = substr(strrchr($value, "@"), 1); // domain only

         foreach($blacklist as $blocked){
             if(str_starts_with($blocked, '@')){
                 //Domain block
                 if($domain === ltrim($blocked, '@')){
                     return new ValidationResult(
                         false,
                         "{$this->field} is blacklisted."
                     );
                 }
             }else{
                 //Exact email block
                 if($value === $blocked){
                     return new ValidationResult(
                         false,
                         "{$this->field} is blacklisted."
                     );
                 }
             }
         }

         return new ValidationResult(true, null);
     }
}
