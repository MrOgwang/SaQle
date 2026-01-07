<?php
namespace SaQle\Session\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Http\Request\RequestIntent;

class SessionMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
         
         if($request->intent === RequestIntent::WEB || $request->intent === RequestIntent::AJAX){
             if(session_status() === PHP_SESSION_NONE){
                 session_start();
             }
         }

         parent::handle($request);
     }
}

