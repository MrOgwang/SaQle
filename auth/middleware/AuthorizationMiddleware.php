<?php
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Auth\Guards\AuthorizationEvaluator;
use SaQle\Auth\Exceptions\AuthorizationException;
/**
* This middleware checks that all permissions defined on a controller are met.
*/
class AuthorizationMiddleware extends IMiddleware {
     
      public function handle(MiddlewareRequestInterface $request){

         $result = AuthorizationEvaluator::authorize($request->route->guards ?? []);

         if(!$result->passed){
             if($result->on_fail){
                 return call_user_func($result->on_fail);
             }

             throw new AuthorizationException('Unauthorized');
         }

         parent::handle($request);
     }
}