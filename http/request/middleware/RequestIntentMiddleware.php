<?php
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Http\Request\RequestIntentResolver;

class RequestIntentMiddleware extends IMiddleware{
     
     public function handle(MiddlewareRequestInterface &$request){

         $request->intent = new RequestIntentResolver()->resolve($request);

     	 parent::handle($request);
     }
}
