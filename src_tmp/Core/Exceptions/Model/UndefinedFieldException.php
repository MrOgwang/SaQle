<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

/**
 * This exception is thrown when an undefined model field name
 * is encountered
 * */

class UndefinedFieldException extends AppException {
     public function __construct(string $message, array $context = []){
         parent::__construct(
             message   : $message,
             code      : HttpStatus::BAD_REQUEST,
             context   : $context
         );
     }
}
