<?php
namespace SaQle\Core\Events;

final class GenericEvent extends Event {

     public function __construct(
         public string $name,
         public EventContext $context
     ) {}

     public static function named(string $name, EventContext $context): static {
        return new static($name, $context);
     }
}
