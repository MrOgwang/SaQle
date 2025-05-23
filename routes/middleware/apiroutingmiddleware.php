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

class ApiRoutingMiddleware extends BaseRoutingMiddleware{

     public function get_routes_from_file(string $path) : mixed {
         if(file_exists($path)){
             $routes = require $path;
             //assert indexed array
             Assert::isList($routes, 'The file at: '.$path.' does not return an indexed array!');
             return $routes;
         }

         return [];
     }

     public function find_and_assign_route(MiddlewareRequestInterface &$request, mixed $routes) : void {
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
             throw new MethodNotAllowedException(url: $_SERVER['REQUEST_URI'], method: $_SERVER['REQUEST_METHOD'], methods: $match->methods);
         }

         //set the appropriate action for the matching route
         $match->action = $match->actions[strtolower($match->method)] ?? strtolower($match->method);

         $request->route = $match;
     }

     public function handle(MiddlewareRequestInterface &$request){
         try{
             //Acquire project level routes.
             $routes = $this->get_routes_from_file(DOCUMENT_ROOT.'/routes/api.php', true);
        
             //Acquire routes for all installed apps.
             foreach(INSTALLED_APPS as $app){
                 $routes = array_merge($routes, $this->get_routes_from_file(DOCUMENT_ROOT.'/apps/'.$app.'/routes/api.php', true));
             }

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
