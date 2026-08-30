<?php
namespace SaQle\Core\Registries;

use SaQle\Middleware\Pipeable;
use RuntimeException;

class HttpMiddlewareRegistry extends MiddlewareRegistry { 

     public function add(
         string $name, 
         string $class, 
         bool   $is_global = true, 
         ?bool  $is_api = null
     ) : void {

         $this->register_middleware($name, $class, $is_global, $is_api, 'http');

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
