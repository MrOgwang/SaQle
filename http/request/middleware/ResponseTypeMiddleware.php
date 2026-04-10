<?php
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;
use SaQle\Http\Response\ResponseTypeResolver;

class ResponseTypeMiddleware extends IMiddleware {
     
     public function handle(Request $request, ?Response $response = null){

         $request->responsetype = new ResponseTypeResolver()->resolve($request);

     	 parent::handle($request, $response);
     }
}
