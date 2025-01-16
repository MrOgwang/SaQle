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
           $targets = $request->is_api_request() ? [$request->route->get_target()] : array_column($request->trail, 'target');
           foreach($targets as $controller){
                $controller = explode("@", $controller)[0];
                $permissions = (new $controller())->get_permissions();
                $allgood = (new class{use PermissionUtils;})::evaluate_permissions($permissions, true);
                if(!$allgood[0]){
                     $redirect_url = $allgood[1] ? $allgood[1] : ROOT_DOMAIN;
                     header('Location: '.$redirect_url);
                }
           }
     	 parent::handle($request);
     }
}
?>