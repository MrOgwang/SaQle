<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class RegexPatternValidator extends IValidator {

     protected function threshold_type(): string {
         return 'string';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         $nullable = $context['nullable'] ?? false;
         $message  = $context['message'] ?? "{$this->field} format is invalid.";

         //1. handle null/empty safely
         if($value === null || $value === ''){
             return $nullable
                ? new ValidationResult(true, null)
                : new ValidationResult(false, "{$this->field} cannot be empty.");
         }

         //2. must be string or stringable
         if(!is_string($value)){
             return new ValidationResult(false, "{$this->field} must be a string.");
         }

         //3. pattern must be provided
         if(!is_string($this->threshold) || trim($this->threshold) === ''){
             return new ValidationResult(false, "Invalid regex pattern supplied.");
         }

         $pattern = $this->normalize_pattern($threshold);

         if($pattern === null){
             return new ValidationResult(false, "Invalid regex pattern format.");
         }

         //4. suppress warnings and check errors manually
         $result = @preg_match($pattern, $value);

         if($result === false){
             return new ValidationResult(false, $this->preg_error_message());
         }

         if(preg_last_error() !== PREG_NO_ERROR){
             return new ValidationResult(false, $this->preg_error_message());
         }

         return $result === 1
            ? new ValidationResult(true, null)
            : new ValidationResult(false, $message);
     }

     //ensure proper delimiters and UTF-8 safety
     private function normalize_pattern(string $pattern): ? string {
         $pattern = trim($pattern);

         //if developer already provided delimiters, trust but verify
         if(preg_match('/^(.).+\\1[imsxuADSUXJu]*$/', $pattern)){
             return $pattern;
         }

         //otherwise auto-wrap with safe delimiter
         $delimiter = '/';

         $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);

         return $delimiter.$escaped.'/'.'u';
     }

     //translate preg error codes to readable messages
     private function preg_error_message(): string {
         return match(preg_last_error()){
             PREG_INTERNAL_ERROR        => 'Internal regex error.',
             PREG_BACKTRACK_LIMIT_ERROR => 'Regex backtrack limit exceeded.',
             PREG_RECURSION_LIMIT_ERROR => 'Regex recursion limit exceeded.',
             PREG_BAD_UTF8_ERROR        => 'Invalid UTF-8 sequence detected.',
             PREG_BAD_UTF8_OFFSET_ERROR => 'Invalid UTF-8 offset.',
             PREG_JIT_STACKLIMIT_ERROR  => 'Regex JIT stack limit exceeded.',
             default                    => 'Unknown regex error.'
         };
     }
}
