<?php
namespace SaQle\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     HttpMessage
};
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Http\Request\Middleware\ResponseTypeMiddleware;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Auth\Middleware\AuthenticationMiddleware;

class MiddlewareGroup {

     public function handle_incoming(Request $request, ?Response $response = null): ?HttpMessage {
         $pre_route = [
             AuthenticationMiddleware::class,
             RoutingMiddleware::class,
             SessionMiddleware::class,
             ResponseTypeMiddleware::class
         ];
         
         $http_message = $this->run_middlewares($pre_route, $request, $response);
         if($http_message){
             return $http_message;
         }

         $middleware = app()->middleware->get_request_middleware($request);
         return $this->run_middlewares($middleware, $request, $response);
     }

     public function handle_outgoing(Request $request, ?Response $response = null): ?HttpMessage {

         $middleware = app()->middleware->get_response_middleware($request);

         return $this->run_middlewares($middleware, $request, $response);
     }

     protected function run_middlewares(array $middlewares, Request $request, ?Response $response = null) : ?HttpMessage {
         if(!$middlewares){
             return null;
         }

         foreach($middlewares as $middleware){

             $instance = resolve($middleware);
             $http_message = $instance->handle($request, $response);

             if($http_message !== null){
                 //STOP everything immediately
                 return $http_message;
             }
         }

         return null;
     }
}
