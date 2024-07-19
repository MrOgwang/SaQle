<?php
namespace SaQle\Permissions\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Route;
use SaQle\Permissions\Utils\PermissionUtils;
/**
* This middleware checks that all permissions defined on a controller are met.
*/
class PermissionsMiddleware extends IMiddleware{
     use PermissionUtils;
     public function handle(MiddlewareRequestInterface &$request){
         $routes        = $request->routes;
         $general_route = null;
         $route_found   = false;
         $redirect_url  = null;
         for($r = 0; $r < count($routes); $r++){
            $permissions = $routes[$r]->get_permissions();
            if(!$permissions){
                $general_route = $routes[$r];
            }else{
                 [$result, $redirect_url] = $this->evaluate_permissions($permissions, true, $request);
                 if($result){
                     $request->final_route = $routes[$r];
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
         }
     	 parent::handle($request);
     }
}
?>