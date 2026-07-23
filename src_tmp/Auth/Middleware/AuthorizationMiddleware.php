<?php
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\RequestMiddleware;
use SaQle\Auth\Guards\GuardEvaluator;
use SaQle\Auth\Exceptions\AuthorizationException;
use SaQle\Http\Response\Message;

/**
* This middleware checks that the user meets route permissions
*/
class AuthorizationMiddleware implements RequestMiddleware {
     
      public function before($request) : ?Message {

         $result = GuardEvaluator::authorize($request->route->guards ?? []);

         if(!$result->passed){
             if($result->on_fail){
                 call_user_func($result->on_fail);
             }

             throw new AuthorizationException('Unauthorized');
         }

         return null;
     }
}