<?php

namespace SaQle\App;

use SaQle\Http\Request\RequestScope;

final class MiddlewareBuilder {

     private array $middleware = [];

     private array $global = [];

     public function add(string $name, string $middleware, ?RequestScope $scope = null) : void {
         $this->middleware[$name] = [
             'scope' => $scope ? $scope->value : null,
             'middleware' => $middleware
         ];
     }

     public function global(array $middleware){
         $this->global = $middleware;
     }

     public function all(): array {
         return $this->middleware;
     }
}