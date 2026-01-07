<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\FatalException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when raw sql cannot be executed
 * */

class RunOperationFailedException extends FatalException{
     public function __construct(array $context){
         parent::__construct(
             message   : "Database operation failed to execute!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
