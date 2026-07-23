<?php
namespace SaQle\Core\Registries;

use SaQle\Http\Request\{
     Request, 
     RequestScope
};
use SaQle\Middleware\{
     RequestMiddleware,
     ResponseMiddleware
};
use SaQle\Middleware\Pipeable;

class HttpMiddlewareRegistry extends MiddlewareRegistry {

     public function add(string $name, string $middleware, ?RequestScope $scope = null) : void {

         $this->stack[$name] = [
             'scope' => $scope ? $scope->value : null,
             'middleware' => $middleware
         ];

         if(is_a($middleware, RequestMiddleware::class, true)){
             $this->before_stack[] = $name;
         }elseif(is_a($middleware, ResponseMiddleware::class, true)){
             $this->after_stack[] = $name;
         }
     }

     protected function filter_middleware(array $stack, Pipeable $pipeable) : array {

         $route_middleware = $pipeable->route->middleware ?? [];

         $shortlisted = [];

         foreach($stack as $name){

             //must either be a global middleware or a route middleware
             if(!in_array($name, $this->global) && !in_array($name, $route_middleware)){
                 continue;
             }

             $record = $this->stack[$name];

             //middleware is either api or web middleware
             if($record['scope'] && $record['scope'] !== $pipeable->scope()){
                 continue;
             }

             $shortlisted[] = $record['middleware'];

         }
         
         return $shortlisted;
     }

}
