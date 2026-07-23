<?php
namespace SaQle\Http\Kernel;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     Message
};

class MiddlewarePipeline {

     public static function run(string $phase, Request $request, ?Response $response = null): ?Message {

         $middleware = app()->http_middleware->get($phase, $request);

         return self::run_middlewares($phase, $middleware, $request, $response);

     }

     protected static function run_middlewares(
         string $phase, 
         array $middlewares, 
         Request $request, 
         ?Response $response = null
     ) : ?Message {
         if(!$middlewares){
             return null;
         }

         foreach($middlewares as $middleware){

             $instance = resolve($middleware);
             $http_message = $phase === 'before' ? 
             $instance->before($request) : 
             $instance->after($request, $response);

             if($http_message !== null){
                 //STOP everything immediately
                 return $http_message;
             }
         }

         return null;
     }
}
