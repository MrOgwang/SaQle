<?php
namespace SaQle\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Http\Request\Middleware\ResponseTypeMiddleware;

class MiddlewareGroup {

     protected function assign_middlewares(MiddlewareInterface $middleware, array $middlewares, int $index = 0){
         if($index < count($middlewares)){
             $next_middleware            = $middlewares[$index];
             $next_middleware_instance   = resolve($next_middleware);
             $middleware->next($next_middleware_instance);
             $this->assign_middlewares($next_middleware_instance, $middlewares, $index + 1);
         }
     }

     public function handle_incoming(Request $request, ?Response $response = null): Request {

         $pre_route = [
             RoutingMiddleware::class,
             ResponseTypeMiddleware::class
         ];
         
         $this->run_chain($pre_route, $request, $response);

         $middleware = app()->middleware->get_request_middleware($request);
         $this->run_chain($middleware, $request, $response);

         return $request;
     }

     public function handle_outgoing(Request $request, ?Response $response = null): Response {

         $middleware = app()->middleware->get_response_middleware($request);

         $this->run_chain($middleware, $request, $response);

         return $response;
     }

     protected function run_chain(array $middlewares, Request $request, ?Response $response = null): void {
         if (!$middlewares) {
             return;
         }

         $middleware = $middlewares[0];
         $instance   = resolve($middleware);

         $this->assign_middlewares($instance, $middlewares, 1);
         $instance->handle($request, $response);
     }
}
