<?php
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Auth\Guards\AuthorizationEvaluator;
use SaQle\Auth\Exceptions\AuthorizationException;
use SaQle\Http\Response\HttpMessage;
/**
* This middleware checks that all permissions defined on a controller are met.
*/
class AuthorizationMiddleware implements MiddlewareInterface {
     
      public function handle($request, $response = null) : ?HttpMessage {

         $result = AuthorizationEvaluator::authorize($request->route->guards ?? []);

         if(!$result->passed){
             if($result->on_fail){
                 call_user_func($result->on_fail);
             }

             throw new AuthorizationException('Unauthorized');
         }

         return null;
     }
}