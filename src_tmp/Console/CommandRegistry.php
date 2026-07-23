<?php

namespace SaQle\Console;

class CommandRegistry {
     protected array $commands = [];

     public function add(CommandDefinition $command): void {
         $this->commands[$command->name] = $command;
     }

     public function find(string $name): ?CommandDefinition {
         return $this->commands[$name] ?? null;
     }

     public function all(): array {
         return $this->commands;
     }
}