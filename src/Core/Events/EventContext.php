<?php
namespace SaQle\Core\Events;

use SaQle\Auth\Models\BaseUser;

final class EventContext {

     public function __construct(
         private ?object $service = null,
         private string $method = '',
         private array $args = [],
         private mixed $result = null,
         private ?BaseUser $user = null,
         public array $attrs = [], //extra attributes to pass along
     ) {}

     public function service(){
         return $this->service;
     }

     public function arg(string $name): mixed {
         return $this->args[$name] ?? null;
     }

     public function args(): array {
         return $this->args;
     }

     public function result(): mixed {
         return $this->result;
     }

     public function user(): ?BaseUser {
         return $this->user;
     }

     public function attr(string $key): mixed {
         return $this->attrs[$key] ?? null;
     }

     public function attrs(string $key): array {
         return $this->attrs;
     }

     public function with_result(mixed $result): self {
         return new self(
             $this->service,
             $this->method,
             $this->args,
             $result,
             $this->user,
             $this->attrs
         );
     }

     public function with_attrs(array $attrs): self {
         return new self(
            $this->service,
            $this->method,
            $this->args,
            $this->result,
            $this->user,
            array_merge($this->attrs, $attrs)
         );
    }
}
