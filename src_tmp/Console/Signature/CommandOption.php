<?php

namespace SaQle\Console\Signature;

class CommandOption {
     public function __construct(
         public readonly string $name,
         public readonly bool $expects_value = true,
         public readonly mixed $default = null,
         public readonly ?string $shortcut = null,
         public readonly ?string $description = null
     ){}
}