<?php
namespace SaQle\Middleware\Group;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Auth\Middleware\AuthMiddleware;

class ApiMiddlewareGroup implements IMiddlewareGroup{
	 public function get_middlewares() : array{
	 	 return [
	 	 	 RoutingMiddleware::class,
	 	 	 AuthMiddleware::class,
	 	 ];
	 }
}
?>