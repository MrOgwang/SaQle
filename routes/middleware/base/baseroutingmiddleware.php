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
     public function get_routes_from_file(string $path) : array{
         if(file_exists($path)){
             $routes = require $path;
             //assert indexed array
             Assert::isList($routes, 'The file at: '.$path.' does not return an indexed array!');
             return $routes;
         }

         return [];
     }

     protected function find_and_assign_route(array $routes, MiddlewareRequestInterface &$request) : void{
         //get a matching route
         $match = null;
         $matches = [false, false];
         foreach($routes as $r){
             $matches = $r->matches();
             if($matches[0] === true){
                 $match = $r;
                 break;
             }
         }

         if(!$match){ //a match wasn't found
             throw new RouteNotFoundException(url: $_SERVER['REQUEST_URI']);
         }

         if(!$matches[1]){ //a match was found with the wrong method
             throw new MethodNotAllowedException(url: $_SERVER['REQUEST_URI'], method: $_SERVER['REQUEST_METHOD'], methods: $match->get_methods());
         }

         //resolve target for matching route
         $target = $match->get_target();
         if(is_callable($target)){
             $match->set_target($target($match->get_params()));
         }

         $request->route = $match;
     }

     protected function assert_all_routes(array $routes) : void{
         //asset array of route objects
         Assert::allIsInstanceOf($routes, Route::class, 'One or more items in routes is not a route object!');
     }
}
?>