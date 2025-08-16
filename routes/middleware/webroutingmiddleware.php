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
use SaQle\Routes\{Router, Route};
use SaQle\Controllers\MediaController;

class WebRoutingMiddleware extends BaseRoutingMiddleware {

     public function find_and_assign_route(MiddlewareRequestInterface &$request, mixed $routes) : void{
         $match = $this->find_matching_route($routes, $request);

         if($match->redirect === true){
             redirect($match->redirect_url);
         }

         $request->route = $match;
         //print_r($request->route);
         $request->trail = $match->get_trail();
         $request->enforce_permissions = false;

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
             //load routes
             $this->load_routes();

             //get all routes.
             $routes = Router::all();

             //add media route
             $routes[] = new Route(MEDIA_URL, MediaController::class);

             $this->find_and_assign_route($request, $routes);
         }catch(RouteNotFoundException $e){
             throw $e;
         }catch(MethodNotAllowedException $e){
             throw $e;
         }catch(\Exception $e){
             throw $e;
         }
     }
}
