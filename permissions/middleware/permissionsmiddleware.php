<?php
namespace SaQle\Permissions\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Route;
use SaQle\Permissions\Utils\PermissionUtils;
use SaQle\Controllers\Refs\ControllerRef;
/**
* This middleware checks that all permissions defined on a controller are met.
*/
class PermissionsMiddleware extends IMiddleware{
     use PermissionUtils;
     public function handle(MiddlewareRequestInterface &$request){
           $targets = $request->is_api_request() ? [$request->route->target] : array_column($request->trail, 'target');
           $controllers = ControllerRef::init()::get_controllers();
           foreach($targets as $controller){
                if(in_array($controller, $controllers)){
                     $permissions = (new $controller())->permissions;

                     $allgood = (new class{use PermissionUtils;})::evaluate_permissions($permissions, true);
                     if(!$allgood[0]){
                          $redirect_url = $allgood[1] ? $allgood[1] : ROOT_DOMAIN;
                          header('Location: '.$redirect_url);
                     }
                }
           }
     	 parent::handle($request);
     }
}
?>