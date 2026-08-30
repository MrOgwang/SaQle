<?php

namespace SaQle\App;

final class MiddlewareBuilder {

     private array $middleware = [];

     public function add(
         string $name, 
         string $class, 
         bool   $is_global = true, 
         ?bool  $is_api = null
     ) : void {
         $this->middleware[$name] = [
             'is_global'  => $is_global,
             'is_api'     => $is_api,
             'middleware' => $class
         ];
     }

     public function all(): array {
         return $this->middleware;
     }
}