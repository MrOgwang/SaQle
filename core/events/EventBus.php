<?php
namespace SaQle\Core\Events;

use SaQle\Core\Registries\EventRegistry;

final class EventBus {

     public function __construct(
        private EventRegistry $registry
     ) {}

     public function dispatch(Event $event): void {
         $key = $event instanceof GenericEvent ? $event->name : $event::class;
         foreach ($this->registry->get_listeners($key) as $listener_class) {
             (new $listener_class)->handle($event);
         }
     }
}
