<?php
namespace SaQle\Core\Registries;

use LogicException;
use SaQle\Security\Validation\Types\ValueType;

class RuleHandlerRegistry {
     protected array $handlers = [];

     public function add(string $rule, ValueType $type, object $handler): void {
         $this->handlers[$rule][$type->value] = $handler;
     }

     public function get(string $rule, ValueType $type): object {
         return $this->handlers[$rule][$type->value] ?? throw new LogicException(
                "No handler for rule [$rule] and type [$type->value]"
         );
     }
}
