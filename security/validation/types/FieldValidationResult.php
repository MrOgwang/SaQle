<?php
namespace SaQle\Security\Validation\Types;

final class FieldValidationResult {

     public function __construct(
         public readonly string $field,
         public readonly bool $isvalid,
         public readonly array $errors = []
     ) {}
}
