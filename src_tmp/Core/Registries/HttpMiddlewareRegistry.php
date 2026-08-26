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
use RuntimeException;

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

         $route_middleware = array_merge($this->global, $pipeable->route->middleware ?? []);

         $shortlisted = [];

         foreach($route_middleware as $name){

             //middleware must be registered
             if(!isset($this->stack[$name])){
                 throw new RuntimeException("The middleware: {$name} not defined!");
             }

             //run before or after stack only
             if(!in_array($name, $stack)){
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
