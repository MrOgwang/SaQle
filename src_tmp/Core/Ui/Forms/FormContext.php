<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

use SaQle\Core\Support\Session;
use SaQle\Http\Request\RuntimeContext;

final class FormContext extends RuntimeContext {
     public function __construct(
         public readonly string  $message = '', //the submit message. mostly available with errors
         public readonly string  $action = '', //route to handle submission
         public readonly string  $method = '', //http verb (post, put)
         public readonly array   $input  = [], //this is the data coming in from a form submit
         public readonly array   $errors = [], //form validation errors, keyed by field name
         public readonly ?object $model  = null
     ) {}

     /**
     * Retrieve a FormContext for a given form from the session.
     * Returns a context with empty arrays if not yet submitted.
     */
     public static function from_session(): self {
         $errors = flash_from_session('__errors', []);
         $input  = flash_from_session('__old', []);

         return new self (
             message: "",
             action: "#",
             method: "POST",
             input: $input,
             errors: $errors,
             model: null
         );
     }
}
