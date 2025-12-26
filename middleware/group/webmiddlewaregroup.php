<?php
namespace SaQle\Middleware\Group;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Auth\Middleware\AuthMiddleware;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Http\Request\Middleware\{DataConsolidatorMiddleware, CsrfMiddleware};
use SaQle\Permissions\Middleware\PermissionsMiddleware;
use SaQle\Http\Cors\Middlewares\CorsMiddleware;
use SaQle\Middleware\AppMiddleware;

class WebMiddlewareGroup implements IMiddlewareGroup {
	 public function get_middlewares() : array {
	 	 $custom_middlewares = app()->middleware->all();

	 	 return [
	 	 	 CorsMiddleware::class,
	 	 	 SessionMiddleware::class,
	 	 	 AuthMiddleware::class,
	 	 	 RoutingMiddleware::class,
             DataConsolidatorMiddleware::class,
             CsrfMiddleware::class,
             PermissionsMiddleware::class,
             ...$custom_middlewares
	 	 ];
	 }
}
