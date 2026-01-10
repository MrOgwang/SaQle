<?php
namespace SaQle\Core\Services\Proxy;

use ReflectionMethod;
use SaQle\Core\Events\EventBus;
use SaQle\Core\Events\EventContext;
use SaQle\Core\Events\EventMetadata;
use SaQle\Core\Events\Event;
use SaQle\Core\Events\GenericEvent;
use SaQle\Http\Request\Request;

final class ServiceProxy {

     public function __construct(
         private object $target,
         private EventBus $event_bus,
         private Request $request
     ) {}

     public function __call(string $method, array $args): mixed {
         $ref_method = new ReflectionMethod($this->target, $method);
         $context = new EventContext(
             service: $this->target,
             method: $method,
             args: $this->map_named_args($ref_method, $args),
             result: null,
             user: $this->request->user
         );

         // BEFORE EVENTS
         foreach (EventMetadata::for_method($ref_method, 'before') as $event_class_or_name) {
             $this->dispatch_event($event_class_or_name, $context);
         }

         // ACTUAL METHOD CALL
         $result = $ref_method->invokeArgs($this->target, $args);
         $context = $context->with_result($result);
        
         // AFTER EVENTS
         foreach (EventMetadata::for_method($ref_method, 'after') as $event_class_or_name) {
             $this->dispatch_event($event_class_or_name, $context);
         }
        
         return $result;
     }

     private function dispatch_event(string $event_class_or_name, EventContext $context): void {
         if (class_exists($event_class_or_name) && is_subclass_of($event_class_or_name, Event::class)){
             $event = $event_class_or_name::from_context($context);
         }else{
             $event = GenericEvent::named($event_class_or_name, $context);
         }

         $this->event_bus->dispatch($event);
     }

     private function map_named_args(ReflectionMethod $method, array $args): array {
         $named = [];
         foreach ($method->getParameters() as $index => $param) {
             $named[$param->getName()] = $args[$index] ?? null;
         }
         return $named;
     }
}
