<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\HttpException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a fetch operation returns a null
 * object
 * */
class NullObjectException extends HttpException{
     public function __construct(array $context){
         parent::__construct(
             message   : "No object was found from table ".$context['table']." that matches your selection creteria",
             code      : FeedBack::NOT_FOUND,
             context   : $context
         );
     }
}
