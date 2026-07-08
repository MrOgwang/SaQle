<?php
namespace SaQle\Core\Events;

use LogicException;

abstract class Event {
     private bool $propagation_stopped = false;

     public static function from_context(EventContext $context): static {
         throw new LogicException('Event must implement from_context() method!');
     }

     public function stop_propagation(): void {
         $this->propagation_stopped = true;
     }

     public function is_propagation_stopped(): bool {
        return $this->propagation_stopped;
     }
}
