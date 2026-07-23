<?php

namespace SaQle\Console;

class ParsedCommand {
     public function __construct(
         public readonly string $command,
         public readonly array $arguments,
         public readonly array $options,
         public readonly array $raw
     ){}
}