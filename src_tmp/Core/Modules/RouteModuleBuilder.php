<?php

namespace SaQle\Core\Modules;

final class RouteModuleBuilder {

     private ?string $prefix = null;

     private ?string $name = null;

     private array $middleware = [];

     private array $layout = [];

     private ?string $authorize = null;

     public function __construct(
         private Module $module
     ){}

     public function prefix(string $prefix) : static {

        $this->prefix = $prefix;

        return $this;
     }

     public function name(string $name) : static {

         $this->name = $name;

         return $this;
     }

     public function middleware(string|array ...$middleware) : static {
         
         $this->middleware = array_merge(
             $this->middleware,
             $middleware
         );

         return $this;
     }

     public function layout(array $layout) : static {

         $this->layout = $layout;

         return $this;
     }

     public function authorize(string $authorize) : static {

         $this->authorize = $authorize;

         return $this;
     }

     public function path(): string {
         return $this->module->path() . '/Routes';
     }

     public function config(): array {
         return [
             'path'       => $this->path(),
             'prefix'     => $this->prefix,
             'name'       => $this->name,
             'middleware' => $this->middleware,
             'domain'     => $this->domain,
             'scope'      => $this->scope,
         ];
     }
}