<?php

namespace SaQle\Security\Validation\Types;

final class ValidationResult {

     public function __construct(
         public readonly bool $isvalid,
         public readonly ?string $message = null,
         public readonly ?mixed $normalized = null,
     ) {}

     public static function pass(mixed $normalized = null): self {
         return new self(true, null, $normalized);
     }

     public static function fail(string $message): self {
         return new self(false, $message, null);
     }
}
