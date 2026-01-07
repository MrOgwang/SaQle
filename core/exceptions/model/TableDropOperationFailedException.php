<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\FatalException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a insert operation fails
 * */

class TableDropOperationFailedException extends FatalException{
     public function __construct(array $context){
         parent::__construct(
             message   : "Table drop operation failed on the table: ".$context['table']."!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
