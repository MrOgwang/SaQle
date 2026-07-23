<?php

namespace SaQle\Console\Signature;

class CommandArgument {
     public function __construct(
         public readonly string $name,
         public readonly bool $required = false,
         public readonly mixed $default = null,
         public readonly ?string $description = null
     ){}
}