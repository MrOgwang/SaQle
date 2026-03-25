<?php
namespace SaQle\Core\Notifications\Mail\Events;

use SaQle\Core\Events\Event;
use SaQle\Core\Events\EventContext;
use SaQle\Core\Support\Mailable;

final class MailSent extends Event {

     public function __construct(
         public Mailable $mail
     ){}

     public static function from_context(EventContext $context): static {
         return new static($context->arg('mail'));
     }
}
