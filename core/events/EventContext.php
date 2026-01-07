<?php
namespace SaQle\Core\Events;

use SaQle\Auth\Models\BaseUser;

final class EventContext {

     public function __construct(
         private object $service,
         private string $method,
         private array $args,
         private mixed $result,
         private ?BaseUser $user
     ) {}

     public function arg(string $name): mixed {
         return $this->args[$name] ?? null;
     }

     public function args(): array {
         return $this->args;
     }

     public function result(): mixed {
         return $this->result;
     }

     public function user(): ?User {
         return $this->user;
     }

     public function with_result(mixed $result): self {
         return new self(
             $this->service,
             $this->method,
             $this->args,
             $result,
             $this->user
         );
     }
}
