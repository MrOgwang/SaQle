<?php
namespace SaQle\Core\Events;

use LogicException;

abstract class Event {
     public static function from_context(EventContext $context): static {
         throw new LogicException('Event must implement from_context() method!');
     }
}
