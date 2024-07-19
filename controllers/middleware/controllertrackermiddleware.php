<?php
namespace SaQle\Controllers\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;

class ControllerTrackerMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
     	 parent::handle($request);
     }
}
?>