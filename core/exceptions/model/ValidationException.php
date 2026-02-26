<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\DomainException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a validation fails
 * on a model or contract
 * */

class ValidationException extends DomainException {

	 public function __construct(array $context){
         parent::__construct(
             message   : $this->format_errors($context['errors'] ?? []),
             code      : FeedBack::BAD_REQUEST,
             context   : $context
         );
     }

     public function errors(): array {
        return $this->get_context()['errors'] ?? [];
     }

     private function format_errors($errors): string {
         $output = "Validation failed.\n\n";

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
