<?php

namespace SaQle\Core\Modules;

use ReflectionClass;
use SaQle\Routing\RouteRegistry;

final class RouteModuleBuilder {

     public function __construct(
         private Module $module
     ){
         //set some defaults
         self::to_registry('name', $module->manifest()->name);

         self::to_registry('prefix', $module->manifest()->name);
     }

     public function prefix(string $prefix) : static {

         self::to_registry('prefix', $prefix);

         return $this;
     }

     public function name(string $name) : static {

         self::to_registry('name', $name);

         return $this;
     }

     public function middleware(string|array ...$middleware) : static {
         
         self::to_registry('middleware', $middleware);

         return $this;
     }

     public function layout(array $layout) : static {

         self::to_registry('layout', $layout);

         return $this;
     }

     public function authorize(string $authorize) : static {

         self::to_registry('authorize', $authorize);

         return $this;
     }

     protected function to_registry(string $entry, mixed $value){

         $name = $module->manifest()->name;

         $modules = RouteRegistry::get_modules() ?? [];

         if(array_key_exists($name, $modules)){
             $modules[$name][$entry] = $value;
         }else{
             $modules[$name] = [$entry => $value];
         } 

         RouteRegistry::modules($modules);
     }
}