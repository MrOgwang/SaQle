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
             if($event->is_propagation_stopped()){
                 break; // Stop dispatching further listeners
             }

             $listener = resolve($listener_class);

             $listener->handle($event);
         }
     }
}
