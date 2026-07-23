<?php
namespace SaQle\Core\Registries;

use SaQle\Http\Request\{
     Request, 
     RequestScope
};
use SaQle\Middleware\Pipeable;

abstract class MiddlewareRegistry {

     protected array $stack = [];

     protected array $global = []; 

     protected array $before_stack = [];

     protected array $after_stack = []; 

     abstract public function add(string $name, string $middleware, ?RequestScope $scope = null) : void;

     abstract protected function filter_middleware(array $stack, Pipeable $pipeable) : array;

     protected function get_before(Pipeable $pipeable) : array {
         return $this->filter_middleware($this->before_stack, $pipeable);
     }

     protected function get_after(Pipeable $pipeable) : array {
         return $this->filter_middleware($this->after_stack, $pipeable);
     }

     public function get(string $phase, Pipeable $pipeable) : array {
         if($phase === 'before'){
             return $this->get_before($pipeable);
         }

         return $this->get_after($pipeable);
     }

     public function set_global(array $global){
         $this->global = $global;
     }
}
