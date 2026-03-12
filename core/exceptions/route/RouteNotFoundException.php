<?php
namespace SaQle\Core\Exceptions\Route;

use SaQle\Core\Exceptions\Base\HttpException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a route matching
 * the incoming request is not found
 * */
class RouteNotFoundException extends HttpException {
     public function __construct(array $context){
         parent::__construct(
             message   : "The resource [".$context['url']."] either does not exist or has been permanently moved!",
             code      : FeedBack::INTERNAL_SERVER_ERROR,
             context   : $context
         );
     }
}
