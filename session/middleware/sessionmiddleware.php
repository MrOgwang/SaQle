<?php
namespace SaQle\Session\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Http\Request\RequestIntent;
use SaQle\Middleware\Interface\ScopedMiddleware;

class SessionMiddleware extends IMiddleware implements ScopedMiddleware{

     public static function scopes(): array {
         return [RequestIntent::WEB, RequestIntent::AJAX];
     }

     public function handle(MiddlewareRequestInterface &$request){
         
         if(session_status() === PHP_SESSION_NONE){
             session_start();
         }

         parent::handle($request);
     }
}

