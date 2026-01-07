<?php
namespace SaQle\Core\Services\Proxy;

use ReflectionMethod;
use SaQle\Core\Events\EventBus;
use SaQle\Core\Events\EventContext;
use SaQle\Core\Events\EventMetadata;
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

         //BEFORE EVENTS
         foreach (EventMetadata::for_method($ref_method, 'before') as $event_class) {
             $this->event_bus->dispatch($event_class::from_context($context));
         }

         //ACTUAL METHOD CALL
         $result = $ref_method->invokeArgs($this->target, $args);

         $context = $context->with_result($result);

         //AFTER EVENTS
         foreach (EventMetadata::for_method($ref_method, 'after') as $event_class){
             $this->event_bus->dispatch(
                 $event_class::from_context($context)
             );
         }

         return $result;
     }

     private function map_named_args(ReflectionMethod $method, array $args): array {
         $named = [];
         foreach ($method->getParameters() as $index => $param) {
             $named[$param->getName()] = $args[$index] ?? null;
         }
         return $named;
     }
}
