<?php
namespace SaQle\Middleware\Base;

use SaQle\Middleware\MiddlewareInterface;

class BaseMiddlewareGroup{
	 protected function assign_middlewares(MiddlewareInterface $middleware, array $middlewares, int $index = 0){
         if($index < count($middlewares)){
             $next_middleware          = $middlewares[$index];
             $next_middleware_instance = new $next_middleware();
             $middleware->next($next_middleware_instance);
             $this->assign_middlewares($next_middleware_instance, $middlewares, $index + 1);
             //print_r($middleware);
         }
     }
}
?>