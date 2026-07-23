<?php

namespace SaQle\App;

final class CommandsBuilder {

     private array $commands = [];

     public function add(string $name, string $command, array $middleware = []){
         $this->commands[$name] = $command;
     }

     public function get() : array {
         return $this->commands;
     }
}