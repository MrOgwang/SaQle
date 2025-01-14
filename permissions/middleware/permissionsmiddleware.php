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
           foreach($request->trail as $t){
                $controller = $t->target;
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