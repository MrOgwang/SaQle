<?php
namespace SaQle\Core\Registries;

class RuleHandlerRegistry {
     protected array $handlers = [];

     public function add(string $rule, string $class): void {
         $this->handlers[$rule] = $class;
     }

     public function get(string $rule): ?string {
         return $this->handlers[$rule] ?? null;
     }

     public function has(string $rule): bool {
         return array_key_exists($rule, $this->handlers);
     }

     public function all(): array {
         return $this->handlers;
     }
}
