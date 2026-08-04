<?php

namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a table rename operation fails
 * */

class TableRenameOperationFailedException extends FrameworkException{
     public function __construct(array $context){
         parent::__construct(
             message   : "Table rename operation failed on the table: ".$context['table']."!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
