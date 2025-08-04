<?php
namespace SaQle\Routes\Middleware\Interface;

use SaQle\Middleware\MiddlewareRequestInterface;

interface IRoutingMiddleware{
	 public function find_and_assign_route(MiddlewareRequestInterface &$request, mixed $routes) : void;
	 public function load_routes(string $type) : mixed;
}
