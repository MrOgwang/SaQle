<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

/**
 * This exception is thrown when raw sql cannot be executed
 * */

class RunOperationFailedException extends AppException{
     public function __construct(array $context){
         parent::__construct(
             message   : "Database operation failed to execute!",
             code      : HttpStatus::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
