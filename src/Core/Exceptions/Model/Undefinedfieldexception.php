<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when an undefined model field name
 * is encountered
 * */

class UndefinedFieldException extends FrameworkException {
     public function __construct(string $message, array $context = []){
         parent::__construct(
             message   : $message,
             code      : FeedBack::BAD_REQUEST,
             context   : $context
         );
     }
}
