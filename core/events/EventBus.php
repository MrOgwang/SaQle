<?php
namespace SaQle\Core\Events;

use SaQle\Core\Registries\EventRegistry;

final class EventBus {

     public function __construct(
        private EventRegistry $registry
     ) {}

     public function dispatch(Event $event): void {
         foreach ($this->registry->get_listeners($event::class) as $listener_class){
             (new $listener_class)->handle($event);
         }
     }
}
