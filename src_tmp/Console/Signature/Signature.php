<?php

namespace SaQle\Console\Signature;

class Signature {
     protected array $arguments = [];

     protected array $options = [];

     public static function make(): static {
         return new static();
     }

     public function argument(
         string $name,
         bool $required = true,
         mixed $default = null,
         ?string $description = null
     ) : static {

         $this->arguments[] = new CommandArgument(
             $name,
             $required,
             $default,
             $description
         );

         return $this;
     }

     public function option(
         string $name,
         mixed $default = null,
         ?string $shortcut = null,
         ?string $description = null
     ) : static {

         $this->options[$name] = new CommandOption(
             $name,
             true,
             $default,
             $shortcut,
             $description
         );

         return $this;
     }

     public function flag(
         string $name,
         ?string $shortcut = null,
         ?string $description = null
     ) : static {

         $this->options[$name] = new CommandOption(
             $name,
             false,
             false,
             $shortcut,
             $description
         );

         return $this;
     }

     public function arguments(): array {
         return $this->arguments;
     }

     public function options(): array {
         return $this->options;
     }
}