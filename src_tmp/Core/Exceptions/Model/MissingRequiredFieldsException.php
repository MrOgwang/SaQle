<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

/**
 * This exception is thrown when the values of required fields of a model
 * have not been provided
 * */

class MissingRequiredFieldsException extends AppException {
     public function __construct(string $message, array $context = []){
         parent::__construct(
             message   : $message,
             code      : HttpStatus::BAD_REQUEST,
             context   : $context
         );
     }
}
