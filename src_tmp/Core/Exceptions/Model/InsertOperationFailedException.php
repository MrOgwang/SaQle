<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

/**
 * This exception is thrown when a insert operation fails
 * */

class InsertOperationFailedException extends AppException{
     public function __construct(array $context){
         parent::__construct(
             message   : "Insert operation failed on the table: ".$context['table']."!",
             code      : HttpStatus::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
