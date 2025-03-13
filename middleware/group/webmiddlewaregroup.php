<?php
namespace SaQle\Middleware\Group;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Routes\Middleware\{RoutingMiddleware, RouteLayoutMiddleware};
use SaQle\Http\Request\Middleware\{DataConsolidatorMiddleware, CsrfMiddleware};
use SaQle\Permissions\Middleware\PermissionsMiddleware;
use SaQle\Config\Middlewares\{VcSetupMiddleware, PageSetupMiddleware};
use SaQle\Http\Cors\Middlewares\CorsMiddleware;

class WebMiddlewareGroup implements IMiddlewareGroup{
	 public function get_middlewares() : array{
	 	 return [
	 	 	 CorsMiddleware::class,
	 	 	 VcSetupMiddleware::class,
	 	 	 PageSetupMiddleware::class,
	 	 	 SessionMiddleware::class,
	 	 	 RoutingMiddleware::class,
             RouteLayoutMiddleware::class,
             DataConsolidatorMiddleware::class,
             CsrfMiddleware::class,
             PermissionsMiddleware::class
	 	 ];
	 }
}
?>