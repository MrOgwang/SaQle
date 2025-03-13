<?php
namespace SaQle\Middleware\Group;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Auth\Middleware\AuthMiddleware;
use SaQle\Http\Request\Middleware\{DataConsolidatorMiddleware, CsrfMiddleware};
use SaQle\Permissions\Middleware\PermissionsMiddleware;
use SaQle\Config\Middlewares\VcSetupMiddleware;
use SaQle\Http\Cors\Middlewares\CorsMiddleware;

class ApiMiddlewareGroup implements IMiddlewareGroup{
	 public function get_middlewares() : array{
	 	 return [
	 	 	 CorsMiddleware::class,
	 	 	 VcSetupMiddleware::class,
	 	 	 RoutingMiddleware::class,
	 	 	 AuthMiddleware::class,
	 	 	 DataConsolidatorMiddleware::class,
	 	 	 CsrfMiddleware::class,
	 	 	 PermissionsMiddleware::class
	 	 ];
	 }
}
?>