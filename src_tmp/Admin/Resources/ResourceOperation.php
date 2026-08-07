<?php

namespace SaQle\Admin\Resources;

class ResourceOperation {

     protected string $name;

     protected ?string $component = null;

     protected array $middleware = [];

     protected array $attributes = [];

     public function __construct(string $name) {
         $this->name = $name;
     }

     public function name(): string {
         return $this->name;
     }

     public function component(string $component): static {

         $this->component = $component;

         return $this;
     }

     public function middleware(array $middleware): static {
         
         $this->middleware = $middleware;

         return $this;
     }

     public function attribute(string $key, mixed $value): static {

         $this->attributes[$key] = $value;

         return $this;
     }

     public function attributes(array $attributes): static {
         
         foreach ($attributes as $key => $value) {
             $this->attributes[$key] = $value;
         }

         return $this;
     }

     public function get_component() : ?string {
         return $this->component;
     }

     public function get_middleware() : array {
         return $this->middleware;
     }

     public function get_attribute(string $key, mixed $default = null): mixed {
         return $this->attributes[$key] ?? $default;
     }

     public function get_attributes(): array {
         return $this->attributes;
     }
}