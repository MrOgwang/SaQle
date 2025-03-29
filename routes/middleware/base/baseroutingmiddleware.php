<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The base routing middleware
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware\Base;

use SaQle\Routes\Middleware\Interface\IRoutingMiddleware;
use SaQle\Core\Assert\Assert;
use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Route;
use SaQle\Routes\Exceptions\{RouteNotFoundException, MethodNotAllowedException};

abstract class BaseRoutingMiddleware extends IMiddleware implements IRoutingMiddleware{
     abstract public function get_routes_from_file(string $path) : mixed;

     abstract public function find_and_assign_route(MiddlewareRequestInterface &$request, mixed $routes) : void;

     protected function assert_all_routes(array $routes) : void{
         //asset array of route objects
         Assert::allIsInstanceOf($routes, Route::class, 'One or more items in routes is not a route object!');
     }
}
?>