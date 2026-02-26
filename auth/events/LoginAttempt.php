<?php
namespace SaQle\Auth\Events;

use SaQle\Core\Events\Event;
use SaQle\Core\Events\EventContext;

final class LoginAttempt extends Event {

     public function __construct(
         public string $strategy_name, 
         public array $credentials
     ){}

     public static function from_context(EventContext $context): static {
         return new static(
             strategy_name: $context->arg('strategy_name'),
             credentials:   $context->arg('credentials')
         );
     }
}
