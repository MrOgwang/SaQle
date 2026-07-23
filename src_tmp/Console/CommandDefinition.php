<?php

namespace SaQle\Console;

class CommandDefinition
{
    public function __construct(
        public string $name,
        public string $class,
        public array $middleware = []
    ) {}
}