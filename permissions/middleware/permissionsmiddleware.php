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
         
     	 parent::handle($request);
     }
}
?>