<?php

namespace SaQle\Middleware;

use SaQle\Console\CommandContext;

class ConsoleMiddlewarePipeline {

     public static function run(string $phase, CommandContext $context, array $extra = []) {

         $middleware = array_merge(
             app()->console_middleware->get($phase, $context),
             $extra
         );

         return self::run_middlewares($phase, $middleware, $context);

     }

     protected static function run_middlewares(string $phase, array $middlewares, CommandContext $context) {
         if(!$middlewares){
             return null;
         }

         foreach($middlewares as $middleware){

             $instance = resolve($middleware);
             $phase === 'before' ? $instance->before($context) :  $instance->after($context);

         }
         
     }
}
