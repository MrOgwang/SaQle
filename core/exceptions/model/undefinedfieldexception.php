<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\FatalException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when an undefined model field name
 * is encountered
 * */

class UndefinedFieldException extends FatalException {
     public function __construct(string $message, array $context = []){
         parent::__construct(
             message   : $message,
             code      : FeedBack::BAD_REQUEST,
             context   : $context
         );
     }
}
