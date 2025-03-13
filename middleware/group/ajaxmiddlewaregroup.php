<?php
namespace SaQle\Middleware\Group;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Http\Request\Middleware\{DataConsolidatorMiddleware, CsrfMiddleware};
use SaQle\Permissions\Middleware\PermissionsMiddleware;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Config\Middlewares\VcSetupMiddleware;
use SaQle\Http\Cors\Middlewares\CorsMiddleware;

class AjaxMiddlewareGroup implements IMiddlewareGroup{
	 public function get_middlewares() : array{
	 	 return [
	 	 	 CorsMiddleware::class,
	 	 	 VcSetupMiddleware::class,
	 	 	 SessionMiddleware::class,
	 	 	 RoutingMiddleware::class,
	 	 	 DataConsolidatorMiddleware::class,
	 	 	 CsrfMiddleware::class,
	 	 	 PermissionsMiddleware::class
	 	 ];
	 }
}
?>