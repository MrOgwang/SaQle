<?php

namespace SaQle\Core\Components;

class ComponentContext {
     public function __construct(
         private array $local,
         private ?ComponentContext $parent = null
     ) {}

     public function get(string $key): mixed {
         return $this->local[$key] ?? $this->parent?->get($key);
     }

     public function expose(): array {
         return $this->parent ? array_merge($this->parent->expose(), $this->local) : $this->local;
     }
}
