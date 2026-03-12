<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\HttpException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when raw sql cannot be executed
 * */

class RunOperationFailedException extends HttpException{
     public function __construct(array $context){
         parent::__construct(
             message   : "Database operation failed to execute!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
