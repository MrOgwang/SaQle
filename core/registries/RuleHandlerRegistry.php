<?php
namespace SaQle\Core\Registries;

class RuleHandlerRegistry {
     protected array $handlers = [];

     public function add(string $rule, string $class, int $priority = 1000): void {
         $this->handlers[$rule] = [
             'validator' => $class,
             'priority'  => $priority
         ];
     }

     public function get(string $rule): ?array {
         return $this->handlers[$rule] ?? null;
     }

     public function has(string $rule): bool {
         return array_key_exists($rule, $this->handlers);
     }

     public function all(): array {
         return $this->handlers;
     }

     public function priority(string $name): int {
         return $this->handlers[$name]['priority'] ?? 1000;
     }
}
