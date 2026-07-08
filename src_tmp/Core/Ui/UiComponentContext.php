<?php

namespace SaQle\Core\Ui;

class UiComponentContext {
     public function __construct(
         private array $local,
         private ?UiComponentContext $parent = null
     ) {}

     public function parent_context(UiComponentContext $parent){
         $this->parent = $parent;
     }

     public function get(string $key): mixed {
         return $this->local[$key] ?? $this->parent?->get($key);
     }

     public function expose(): array {
         return $this->parent ? array_merge($this->parent->expose(), $this->local) : $this->local;
     }
}
