<?php

namespace SaQle\Console;

use SaQle\Core\Support\AttributeBag;
use SaQle\Middleware\Pipeable;

class CommandContext implements Pipeable {
     public function __construct(
         protected string $command,
         protected array $arguments = [],
         protected array $options = [],
         protected array $raw = [],
         protected Input $input = new Input(),
         protected Output $output = new Output(),
         public AttributeBag $attributes = new AttributeBag()
     ) {}

     public function command(): string {
         return $this->command;
     }

     public function argument(string $name, mixed $default = null): mixed {
         return $this->arguments[$name] ?? $default;
     }

     public function option(string $name, mixed $default = null): mixed {
         return $this->options[$name] ?? $default;
     }

     public function has_option(string $name): bool {
         return array_key_exists($name, $this->options);
     }

     public function arguments(): array {
         return $this->arguments;
     }

     public function options(): array{
         return $this->options;
     }

     public function raw(): array{
         return $this->raw;
     }

     public function input(): Input{
         return $this->input;
     }

     public function output(): Output{
         return $this->output;
     }
}