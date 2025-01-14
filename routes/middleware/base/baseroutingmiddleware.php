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
         foreach($routes as $r){
             if($r->matches()){
                 $match = $r;
                 break;
             }
         }

         if(!$match){
            throw new \Exception("The route requested was not found!");
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