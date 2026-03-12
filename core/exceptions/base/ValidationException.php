<?php
namespace SaQle\Core\Exceptions\Base;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

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

class ValidationException extends FrameworkException {

     protected string $safe_message = "Validation failed.";

     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct($message, FeedBack::UNPROCESSABLE_ENTITY, $data, $prev);
     }

     public function errors(): array {
        return $this->get_context()['errors'] ?? [];
     }

     public function format_errors($errors): string {

         $errors = $this->errors();

         $output = "Validation errors: \n\n";

         foreach($errors as $field => $messages){
             $output .= strtoupper($field) . ":\n";

             foreach($messages as $message) {
                 $output .= "  • {$message}\n";
             }

             $output .= "\n";
         }

         return trim($output);
     }
}
