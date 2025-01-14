<?php
namespace SaQle\Middleware\Group;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Routes\Middleware\{RoutingMiddleware, RouteLayoutMiddleware};
use SaQle\Http\Request\Middleware\DataConsolidatorMiddleware;
use SaQle\Auth\Middleware\AuthMiddleware;
use SaQle\Permissions\Middleware\PermissionsMiddleware;

class WebMiddlewareGroup implements IMiddlewareGroup{
	 public function get_middlewares() : array{
	 	 return [
	 	 	 SessionMiddleware::class,
             AuthMiddleware::class,
             RoutingMiddleware::class,
             RouteLayoutMiddleware::class,
             DataConsolidatorMiddleware::class,
             PermissionsMiddleware::class
	 	 ];
	 }
}
?>