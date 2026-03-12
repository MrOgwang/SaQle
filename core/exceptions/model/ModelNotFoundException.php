<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\HttpException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a model or table nameis not
 * found in a database context class
 * */

class ModelNotFoundException extends HttpException {

     public function __construct(string $name, array $context = []){
         parent::__construct(
             message   : "The model or table named [{$name}] does not exist!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
     
}
