<?php

namespace SaQle\Core\Config;

final class ConfigRepository {
    
     private array $items = [];
     private array $stack = [];

     public function __construct(array $items){
         $this->items = $items;
     }

     public function get(string $key, mixed $default = null): mixed {
         return $this->items[$key] ?? $default;
     }

     public function set(string $key, mixed $value): void {
         $this->items[$key] = $value;
     }

     public function has(string $key): bool {
         return array_key_exists($key, $this->items);
     }

     public function all(): array {
         return $this->items;
     }

     public function namespace(string $prefix): array{
         return array_filter(
             $this->items,
             fn($k) => str_starts_with($k, $prefix),
             ARRAY_FILTER_USE_KEY
         );
     }

     public function push(array $overrides): void {
         $this->stack[] = $this->items;
         $this->items = array_replace($this->items, $overrides);
     }

     public function pop(): void {
         $this->items = array_pop($this->stack);
     }
}
