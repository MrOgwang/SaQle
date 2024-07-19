<?php
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Manager\RouteManager;

/**
* This middleware injects the found route into the request object.
*/
class RoutingMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
         $routing_manager = new RouteManager();
         $request->routes = $routing_manager->get_selected_routes();
     	 parent::handle($request);
     }
}
?>