<?php

namespace SaQle\Console;

use SaQle\Http\Request\Data\Data;
use SaQle\Middleware\Pipeable;

class CommandContext implements Pipeable {

     private static $instance;
     
     private function __construct(
         protected string $command = "",
         protected array $arguments = [],
         protected array $options = [],
         protected array $raw = [],
         protected Input $input = new Input(),
         protected Output $output = new Output(),
         public Data $attributes = new Data()
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

     public static function init(
         string $command = "",
         array $arguments = [],
         array $options = [],
         array $raw = []
     ) : CommandContext {
         return self::$instance ??= new self(
             command: $command,
             arguments: $arguments,
             options: $options,
             raw: $raw
         );
     }
}