<?php
namespace SaQle\Middleware;
abstract class IMiddleware implements MiddlewareInterface{
	 private ?MiddlewareInterface $middleware = null;
     public function next(MiddlewareInterface $middleware) : MiddlewareInterface{
     	$this->middleware = $middleware;
     	return $this;
     }
     public function handle(MiddlewareRequestInterface &$request){
     	 if($this->middleware){
     		 $this->middleware->handle($request);
     	 }
     }
}
?>