<?php
namespace SaQle\Auth\Events;

use SaQle\Core\Events\Event;
use SaQle\Core\Events\EventContext;
use SaQle\Auth\Interfaces\UserInterface;

final class Logout extends Event {

     public function __construct(
         public ?UserInterface $user = null
     ){}

     public static function from_context(EventContext $context): static {
         return new static(user: $context->result()->user);
     }
}
