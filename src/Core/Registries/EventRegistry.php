<?php
namespace SaQle\Core\Registries;

class EventRegistry {

     protected array $listeners = [];

     public function add(string $key, array $listener_classes): void {
         foreach ($listener_classes as $listener_class) {
             $this->listeners[$key][] = $listener_class;
             $this->listeners[$key] = array_unique($this->listeners[$key]);
         }
     }

     public function get_listeners(string $key): array {
         return $this->listeners[$key] ?? [];
     }

     //For caching support
     public function get_all_listeners(): array {
         return $this->listeners;
     }
}
