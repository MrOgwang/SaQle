<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

/**
 * This exception is thrown when a fetch operation returns a null
 * object
 * */
class NullObjectException extends AppException{
     public function __construct(array $context){
         parent::__construct(
             message   : "No object was found from table ".$context['table']." that matches your selection creteria",
             code      : HttpStatus::NOT_FOUND,
             context   : $context
         );
     }
}
