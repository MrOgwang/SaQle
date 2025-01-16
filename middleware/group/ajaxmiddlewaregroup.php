<?php
namespace SaQle\Middleware\Group;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Http\Request\Middleware\DataConsolidatorMiddleware;
use SaQle\Permissions\Middleware\PermissionsMiddleware;
use SaQle\Session\Middleware\SessionMiddleware;

class AjaxMiddlewareGroup implements IMiddlewareGroup{
	 public function get_middlewares() : array{
	 	 return [
	 	 	 SessionMiddleware::class,
	 	 	 RoutingMiddleware::class,
	 	 	 DataConsolidatorMiddleware::class,
	 	 	 PermissionsMiddleware::class
	 	 ];
	 }
}
?>