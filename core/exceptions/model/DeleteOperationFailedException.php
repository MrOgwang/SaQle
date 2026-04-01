<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a delete operation fails
 * */

class DeleteOperationFailedException extends FrameworkException{
     public function __construct(array $context){
         parent::__construct(
             message   : "Delete operation failed on the table: ".$context['table']."!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
