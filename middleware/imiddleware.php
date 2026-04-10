<?php
namespace SaQle\Middleware;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;

abstract class IMiddleware implements MiddlewareInterface {

	 private ?MiddlewareInterface $middleware = null;

     public function next(MiddlewareInterface $middleware) : MiddlewareInterface {
     	 $this->middleware = $middleware;
     	 return $this;
     }

     public function handle(Request $request, ?Response $response = null){
     	 if($this->middleware){
     		 $this->middleware->handle($request, $response);
     	 }
     }
}
