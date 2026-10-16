<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;
use Throwable;

/**
 * Purpose: 
 * - User input failed validation
 * 
 * When it happens:
 * 1. Form validation fails
 * 2. Invalid request data
 * 3. Missing required fields
 * 4. Wrong input format
 * 
 * Typical response
 * - Redirect back
 * - Preserve old input
 * - Show field errors
 * 
 * */

class ValidationException extends AppException {

     protected string $safe_message = "Validation failed";

     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct(
             $message ?: $this->format_errors($context['errors'] ?? []), 
             HttpStatus::UNPROCESSABLE_ENTITY, 
             $context, 
             $prev
         );
     }

     public function errors(): array {
        return $this->getContext()['errors'] ?? [];
     }

     public function input(): array {
        return $this->getContext()['input'] ?? [];
     }

     private function format_errors($errors) : string {
         $output = $this->safe_message.": \n\n";

         foreach($errors as $field => $messages){
             $output .= strtoupper($field).":\n";

             foreach($messages as $message) {
                 $output .= "  • {$message}\n";
             }
             
             $value_provided = $this->input()[$field] ?? "";

             $output .= "Value provided: {$value_provided}\n";

             $output .= "\n";
         }

         return trim($output);
     }
}
