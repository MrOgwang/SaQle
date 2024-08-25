<?php
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Manager\RouteManager;
use SaQle\Permissions\Utils\PermissionUtils;

/**
* This middleware injects the found route into the request object.
*/
class RoutingMiddleware extends IMiddleware{
     use PermissionUtils;
     public function handle(MiddlewareRequestInterface &$request){
         $routing_manager = new RouteManager();
         $webroutes = $routing_manager->get_web_routes()->get_routes();
         $selected_routes = $routing_manager->get_selected_routes();

         /**
         * Sort the selected routes based on the number of permission classes,
         * the idea is to resolve routes with the most number of permissions first
         * */
         usort($selected_routes, function($a, $b) {
             return count($a->get_permissions()) <=> count($b->get_permissions());
         });
         $selected_routes = array_reverse($selected_routes);

         /**
          * Eliminate the routes whose permission classes fail.
          * */
         $selected_routes = array_filter($selected_routes, function($r) use ($request){
             $permissions = $r->get_permissions();
             if(!$permissions)
                return true;

             [$result, $redirect_url] = $this->evaluate_permissions($permissions, true, $request);
             if($result)
                return true;

             return false;
         });

         $selected_routes = array_values($selected_routes);
         if(!$selected_routes){
              header("Location: ".ROOT_DOMAIN);
         }

         $request->final_route = $selected_routes[0];

         /*$general_route = null;
         $route_found   = false;
         $redirect_url  = null;
         for($r = 0; $r < count($selected_routes); $r++){
            $permissions = $selected_routes[$r]->get_permissions();
            if(!$permissions){
                $general_route = $selected_routes[$r];
            }else{
                 [$result, $redirect_url] = $this->evaluate_permissions($permissions, true, $request);
                 if($result){
                     $request->final_route = $selected_routes[$r];
                     $route_found          = true;
                     break;
                 }
            }
         }

         if(!$route_found && $general_route){
             $request->final_route = $general_route;
         }elseif(!$route_found && !$general_route){
             if($redirect_url){
                header("Location: ".$redirect_url);
                exit;
             }

             //if redirect url is not provided, sign the user out and redirect to root url
             session_start();
             session_destroy();
             header('Location: '.ROOT_DOMAIN);
             die();
         }*/

         $rout_trail = [PAGE_CONTROLLER_CLASS];
         if($request->final_route->get_url()){
             //Come back to this later
             $toprocess = [];
             $final_route_url = $request->final_route->get_url();
             foreach($webroutes as $route){
                 $url2 = $route->get_url();
                 if(str_contains($final_route_url , $url2)){
                     $toprocess[] = $route;
                 }
             }
         }else{
             $rout_trail[] = $request->final_route->get_target()[0];
         }

         $request->final_route->set_trail($rout_trail);

         $request->routes = $selected_routes;
     	 parent::handle($request);
     }

     private function process(){

     }
}
?>