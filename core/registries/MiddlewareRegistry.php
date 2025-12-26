<?php
namespace SaQle\Core\Registries;

class MiddlewareRegistry {
     private array $stack = [];

     public function register(string $middleware): void {
         $this->stack[] = $middleware;
     }

     public function all(): array {
         return $this->stack;
     }
}
