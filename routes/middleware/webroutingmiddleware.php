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
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Middleware\Base\BaseRoutingMiddleware;
use SaQle\Routes\Exceptions\{RouteNotFoundException, MethodNotAllowedException};
use SaQle\Controllers\Refs\ControllerRef;

class WebRoutingMiddleware extends BaseRoutingMiddleware{

     private function extract_trail(&$request, array $matches, array $trail = [], array $params = [], array $queries = []){
         if(isset($matches[2])){
             $route   = $matches[2];
             $trail[] = (Object)['url' => $route->url, 'target' => $route->target, 'action' => $route->action];
             $params  = array_merge($params, $route->params->get_all());
             $queries = array_merge($queries, $route->queries->get_all());
         }

         if(is_array($matches[1])){
             $trail = $this->extract_trail($request, $matches[1], $trail, $params, $queries);
         }else{
             if(!$matches[1]){ //a match was found with the wrong method
                 throw new MethodNotAllowedException(url: $_SERVER['REQUEST_URI'], method: $_SERVER['REQUEST_METHOD'], methods: $matches[2]->methods);
             }

             //set the appropriate action for the matching route
             $match          = $matches[2];
             $match->action  = $match->actions[strtolower($match->method)] ?? strtolower($match->method);
             foreach($queries as $q => $qv){
                 $match->queries->set($q, $qv);
             }
             foreach($params as $p => $pv){
                 $match->params->set($p, $pv);
             }
             $trail[count($trail) - 1]->action = $match->action;

             $request->route = $match;
         }

         return $trail;
     }

     public function get_routes_from_file(string $path) : mixed {
         if(file_exists($path)){
             return require $path;
         }

         return null;
     }

     public function find_and_assign_route(MiddlewareRequestInterface &$request, mixed $routes) : void{
         $matches = $routes->matches();
         if(!$matches[0]){
             throw new RouteNotFoundException(url: $_SERVER['REQUEST_URI']);
         }
         $request->trail = $this->extract_trail($request, $matches, []);

         $controllers = ControllerRef::init()::get_controllers();
         $targets = array_column($request->trail, 'target');
         foreach($targets as $controller){
             if(in_array($controller, $controllers)){
                 $permissions = (new $controller())->permissions;
                 if($permissions){
                     $request->enforce_permissions = true;
                     break;
                 }
             }
         }
     }

     public function handle(MiddlewareRequestInterface &$request){
         try{
             //Acquire project level routes.
             $layoutroute = $this->get_routes_from_file(DOCUMENT_ROOT.'/routes/web.php', true);
             $this->find_and_assign_route($request, $layoutroute);
         }catch(RouteNotFoundException $e){
             throw $e;
         }catch(MethodNotAllowedException $e){
             throw $e;
         }catch(\Exception $e){
             throw $e;
         }
     }
}
?>