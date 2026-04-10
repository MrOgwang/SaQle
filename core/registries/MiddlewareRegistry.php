<?php
namespace SaQle\Core\Registries;

use SaQle\Http\Request\{
     Request, 
     RequestScope
};
use SaQle\Core\Assert\Assert;

class MiddlewareRegistry {

     private array $stack = [];

     private array $request_stack = [];

     private array $response_stack = [];

     public function add(string $name, string $middleware, ?RequestScope $scope = null) : void {
         $this->stack[$name] = [
             'scope' => $scope ? $scope->value : null,
             'middleware' => $middleware
         ];
     }

     public function request(array $request_stack) : void {
         $this->request_stack = array_merge($this->request_stack, $request_stack);
     }

     public function response(array $response_stack) : void {
         $this->response_stack = array_merge($this->response_stack, $response_stack);
     }

     private function filter_middleware(array $stack, Request $request){
        
         $shortlisted = [];

         foreach($stack as $key => $routes){

             $has_routes = is_int($key) ? false : true;
             $middleware_name = $has_routes ? $key : $routes;
             $record = $this->stack[$middleware_name];

             if(!$record['scope'] || ($record['scope'] === $request->route->scope)){
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

     public function get_request_middleware(Request $request) : array {
         return $this->filter_middleware($this->request_stack, $request);
     }

     public function get_response_middleware(Request $request) : array {
         return $this->filter_middleware($this->response_stack, $request);
     }
}
