<?php

namespace SaQle\Console;

class Output
{
    public function line(string $text = ''): void
    {
        echo $text.PHP_EOL;
    }

    public function success(string $text): void
    {
        echo "\033[32m✔ {$text}\033[0m".PHP_EOL;
    }

    public function error(string $text): void
    {
        echo "\033[31m✖ {$text}\033[0m".PHP_EOL;
    }

    public function warning(string $text): void
    {
        echo "\033[33m⚠ {$text}\033[0m".PHP_EOL;
    }

    public function info(string $text): void
    {
        echo "\033[36m{$text}\033[0m".PHP_EOL;
    }
}