<?php

namespace SaQle\Core\Components;

use SaQle\Core\Data\Data;

class Context {

     public function __construct(
         private Data $props = new Data(),
         private Data $data = new Data(),
         private ?Context $parent = null
     ){}

     public function parent(Context $parent): static {
         
         $this->parent = $parent;

         return $this;
     }

     public function props(): Data {
         return $this->props;
     }

     public function data(): Data {
         return $this->data;
     }

     public function get(string $key, mixed $default = null): mixed {

         if($this->data->has($key)){
             return $this->data->get($key);
         }

         if($this->props->has($key)){
             return $this->props->get($key);
         }

         return $this->parent?->get($key, $default) ?? $default;
     }

     public function expose(): array {

         $exposed = $this->parent ? $this->parent->expose() : [];

         return array_merge($exposed, $this->props->get_all(), $this->data->get_all());
     }

     public function expose_props(): array {

         $exposed = $this->parent ? $this->parent->expose_props() : [];

         return array_merge($exposed, $this->props->get_all());
     }

     public function expose_data(): array {

         $exposed = $this->parent ? $this->parent->expose_data() : [];

         return array_merge($exposed, $this->data->get_all());
     }
}