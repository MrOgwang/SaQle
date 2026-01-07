<?php
namespace SaQle\Core\Registries;

final class EventRegistry {

     private array $listeners = [];

     public function add(string $event_class, array $listener_classes): void {
         foreach ($listener_classes as $listener_class) {
             $this->listeners[$event_class][] = $listener_class;
         }
     }

     public function get_listeners(string $event_class): array {
         return $this->listeners[$event_class] ?? [];
     }
}
