<?php
namespace SaQle\Core\Registries;

use SaQle\Http\Request\{
     Request,
     RequestScope
};
use SaQle\Middleware\Pipeable;
use SaQle\Console\Middleware\{
     BeforeCommandMiddleware,
     AfterCommandMiddleware
};
use SaQle\Middleware\{
     RequestMiddleware,
     ResponseMiddleware
};

abstract class MiddlewareRegistry {

     protected array $stack = [];

     protected array $global = []; 

     protected array $before_stack = [];

     protected array $after_stack = []; 

     abstract public function add(
         string $name, 
         string $class, 
         bool   $is_global = true, 
         ?bool  $is_api = null
     ) : void;

     protected function register_middleware(
         string $name, 
         string $class, 
         bool   $is_global = true, 
         ?bool  $is_api = null,
         string $type = "http"
     ){
         $before_class = RequestMiddleware::class;
         $after_class  = ResponseMiddleware::class;

         if($type === 'console'){
             $before_class = BeforeCommandMiddleware::class;
             $after_class  = AfterCommandMiddleware::class;
         }

         $scope = is_null($is_api) ? RequestScope::ALL : ($is_api ? RequestScope::API : RequestScope::WEB);

         $this->stack[$name] = [
             'scope' => $scope->value,
             'middleware' => $class
         ];

         if(is_a($class, $before_class, true)){
             $this->before_stack[] = $name;
         }elseif(is_a($class, $after_class, true)){
             $this->after_stack[] = $name;
         }

         if($is_global){
             $this->global[] = $name;
         }
     }

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
}
