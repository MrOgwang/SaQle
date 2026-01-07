<?php
namespace SaQle\Middleware\Factory;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Middleware\Base\BaseMiddlewareGroup;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Auth\Middleware\AuthenticationMiddleware;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Http\Request\Middleware\{DataMiddleware, CsrfMiddleware};
use SaQle\Auth\Middleware\AuthorizationMiddleware;
use SaQle\Http\Cors\Middlewares\CorsMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Http\Request\Request;

class MiddlewareGroup extends BaseMiddlewareGroup implements IMiddlewareGroup{
	 public function get_middlewares() : array {
	 	 $custom_middlewares = app()->middleware->all();

	 	 return [
	 	 	 RoutingMiddleware::class,
	 	 	 CorsMiddleware::class,
	 	 	 SessionMiddleware::class,
	 	 	 AuthenticationMiddleware::class,
             DataMiddleware::class,
             CsrfMiddleware::class,
             AuthorizationMiddleware::class,
             ...$custom_middlewares
	 	 ];
	 }

	 public function handle(MiddlewareRequestInterface &$request) : Request {
	 	 $request_middlewares = $this->get_middlewares();
	 	 if($request_middlewares){
	 	 	 $middleware          = $request_middlewares[0];
             $middleware_instance = new $middleware();
             $this->assign_middlewares($middleware_instance, $request_middlewares, 1);
             $middleware_instance->handle($request);
	 	 }

	 	 return $request;
	 }
}
