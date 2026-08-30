<?php

namespace SaQle\Core\Registries;

use SaQle\Middleware\Pipeable;

class ConsoleMiddlewareRegistry extends MiddlewareRegistry {

     public function add(
         string $name, 
         string $class, 
         bool   $is_global = true, 
         ?bool  $is_api = null
     ) : void {

         $this->register_middleware($name, $middleware, $is_global, $is_api, 'console');

     }

     protected function filter_middleware(array $stack, Pipeable $pipeable) : array {

         $route_middleware = $request->route->middleware ?? [];

         $shortlisted = [];

         foreach($stack as $name){

             //must either be a global middleware or a route middleware
             if(!in_array($name, $this->global) && !in_array($name, $route_middleware)){
                 continue;
             }

             $record = $this->stack[$name];

             //middleware is either api or web middleware
             if($record['scope'] && $record['scope'] !== $request->scope()){
                 continue;
             }

             $shortlisted[] = $record['middleware'];

         }
         
         return $shortlisted;
     }
}
