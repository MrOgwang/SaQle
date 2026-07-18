<?php
namespace SaQle\Core\Registries;

use SaQle\Http\Request\{
     Request, 
     RequestScope
};
use SaQle\Core\Assert\Assert;
use SaQle\Middleware\{
     RequestMiddleware,
     ResponseMiddleware
};

class MiddlewareRegistry {

     private array $stack = [];

     private array $request_stack = [];

     private array $response_stack = []; 

     public function add(string $name, string $middleware, ?RequestScope $scope = null) : void {

         $this->stack[$name] = [
             'scope' => $scope ? $scope->value : null,
             'middleware' => $middleware
         ];

         if(is_a($middleware, RequestMiddleware::class, true)){
             $this->request_stack[] = $name;
         }elseif(is_a($middleware, ResponseMiddleware::class, true)){
             $this->response_stack[] = $name;
         }
     }

     private function filter_middleware(array $stack, Request $request){
         $shortlisted = [];

         foreach($stack as $key => $routes){

             $has_routes = is_int($key) ? false : true;
             $middleware_name = $has_routes ? $key : $routes;
             $record = $this->stack[$middleware_name];

             if(!$record['scope'] || ($record['scope'] === $request->route->scope->value)){
                 if(!$has_routes){
                     $shortlisted[] = $record['middleware'];
                     continue;
                 }

                 Assert::isNonEmptyList($routes, 'Middleware routes must be an array of route names!');

                 if(in_array($request->route->name, $routes)){
                     $shortlisted[] = $record['middleware'];
                 }
             }
         }
         
         return $shortlisted;
     }

     private function get_before(Request $request) : array {
         return $this->filter_middleware($this->request_stack, $request);
     }

     private function get_after(Request $request) : array {
         return $this->filter_middleware($this->response_stack, $request);
     }

     public function get(string $phase, Request $request) : array {
         if($phase === 'before'){
             return $this->get_before($request);
         }

         return $this->get_after($request);
     }
}
