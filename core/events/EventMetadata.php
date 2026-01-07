<?php
namespace SaQle\Core\Events;

use SaQle\Core\Events\Attributes\Emits;
use ReflectionMethod;

final class EventMetadata {

     public static function for_method(
         ReflectionMethod $method,
         string $phase
     ) : array {
         $attributes = $method->getAttributes(Emits::class);

         if (!$attributes) {
             return [];
         }

         $instance = $attributes[0]->newInstance();

         return $phase === 'before' ? $instance->before : $instance->after;
     }
}
