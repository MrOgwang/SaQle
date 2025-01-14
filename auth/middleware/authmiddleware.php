<?php
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Auth\Services\AuthService;
use SaQle\Auth\Observers\SigninObserver;

class AuthMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
     	 parent::handle($request);
     }
}
?>