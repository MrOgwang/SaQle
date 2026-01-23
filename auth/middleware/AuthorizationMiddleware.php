<?php
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Auth\Guards\AuthorizationEvaluator;
use SaQle\Core\Exceptions\Http\UnauthorizedException;
/**
* This middleware checks that all permissions defined on a controller are met.
*/
class AuthorizationMiddleware extends IMiddleware{
     
      public function handle(MiddlewareRequestInterface &$request){
           
           if(!AuthorizationEvaluator::authorize($request->route->guards ?? [])){
                throw new UnauthorizedException('Unauthorized');
           }

     	 parent::handle($request);
      }
}