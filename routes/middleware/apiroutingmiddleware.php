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
 * The routing middleware is responsible for the following:
 * 1. checks if the route requested is defined
 * 2. checks if the request method is valid
 * 3. determine whether the route selected requires permissions or not
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Middleware\Base\BaseRoutingMiddleware;
use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Request\Processors\ApiRequestProcessor;
use SaQle\Routes\Exceptions\{RouteNotFoundException, MethodNotAllowedException};
use SaQle\Core\Assert\Assert;
use SaQle\Routes\{Router, Route};

class ApiRoutingMiddleware extends BaseRoutingMiddleware{

     public function find_and_assign_route(MiddlewareRequestInterface &$request, mixed $routes) : void {
         $match = $this->find_matching_route($routes, $request);
         $request->route = $match;
     }

     public function handle(MiddlewareRequestInterface &$request){
         try{
             //load routes
             $this->load_routes(type: 'api');

             //get all routes.
             $routes = Router::all();

             $this->assert_all_routes($routes);

             $this->find_and_assign_route($request, $routes);

             $controller = $request->route->target;

             $permissions = (new $controller())->permissions;

             if($permissions){
                 $request->enforce_permissions = true;
             }
         }catch(RouteNotFoundException $e){
             not_found_exception($e->get_message());
         }catch(MethodNotAllowedException $e){
             method_not_allowed_exception($e->get_message());
         }catch(\Exception $e){
             internal_server_error_exception($e->getMessage());
         }
     }
}
