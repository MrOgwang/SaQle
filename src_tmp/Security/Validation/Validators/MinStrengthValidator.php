<?php
namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class MinStrengthValidator extends IValidator {
     protected function threshold_type(): string {
         return 'int';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //threshold must be integer between 1 and 5
         if(!is_int($this->threshold) || $this->threshold < 1 || $this->threshold > 5) {
             return new ValidationResult(
                 false,
                 "min_strength rule for {$this->field} must be an integer between 1 and 5."
             );
         }

         //value must be string
         if(!is_string($value)){
             return new ValidationResult(
                 false,
                 "{$this->field} must be a valid password."
             );
         }

         $value = trim($value);

         if($value === ''){
             return new ValidationResult(
                false,
                "{$this->field} must be a valid password."
             );
         }

         $score = 0;
         $length = strlen($value);

         //base length requirement
         if($length >= 8){
             $score++;
         }

         if(preg_match('/[a-z]/', $value)){
             $score++;
         }

         if(preg_match('/[A-Z]/', $value)){
             $score++;
         }

         if(preg_match('/\d/', $value)){
             $score++;
         }

         if(preg_match('/[^a-zA-Z\d]/', $value)){
             $score++;
         }

         //bonus for long passwords
         if($length >= 12){
             $score++;
         }

         //cap score at 5
         $score = min($score, 5);

         if($score < $threshold){
             return new ValidationResult(
                 false,
                 "{$this->field} does not meet the required strength level ({$this->threshold})."
             );
         }

         return new ValidationResult(true, null);
     }
}
