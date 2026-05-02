<?php
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Response\ResponseTypeResolver;
use SaQle\Http\Response\HttpMessage;

class ResponseTypeMiddleware implements MiddlewareInterface {
     
     public function handle($request, $response = null) : ?HttpMessage {

         $request->responsetype = new ResponseTypeResolver()->resolve($request);

         return null;
         
     }
}
