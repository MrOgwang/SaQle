<?php
namespace SaQle\Core\Exceptions\Route;

use SaQle\Core\Exceptions\Base\HttpException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when the wrong http verb is
 * provided for a route
 * */

class MethodNotAllowedException extends HttpException{
     public function __construct(array $context){
         parent::__construct(
             message   : "The request method [".$context['method']."] is not allowed for the resource [".$context['url']."].  Valid methods are [".implode(',', $context['methods'])."]!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
