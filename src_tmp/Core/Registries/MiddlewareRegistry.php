<?php
namespace SaQle\Core\Registries;

use SaQle\Http\Request\{
     Request, 
     RequestScope
};

abstract class MiddlewareRegistry {

     protected array $stack = [];

     protected array $global = []; 

     protected array $before_stack = [];

     protected array $after_stack = []; 


     abstract public function add(string $name, string $middleware, ?RequestScope $scope = null) : void;

     abstract protected function filter_middleware(array $stack, Request $request) : array;

     protected function get_before(Request $request) : array {
         return $this->filter_middleware($this->before_stack, $request);
     }

     protected function get_after(Request $request) : array {
         return $this->filter_middleware($this->after_stack, $request);
     }

     public function get(string $phase, Request $request) : array {
         if($phase === 'before'){
             return $this->get_before($request);
         }

         return $this->get_after($request);
     }

     public function set_global(array $global){
         $this->global = $global;
     }
}
