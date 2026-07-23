<?php

namespace SaQle\Console;

class Input {
    public function ask(string $question): string
    {
        echo $question.' ';

        return trim(fgets(STDIN));
    }

    public function confirm(string $question): bool
    {
        return strtolower(
            $this->ask($question.' [y/N]')
        ) === 'y';
    }

    public function secret(string $question): string
    {
        echo $question.' ';

        shell_exec('stty -echo');

        $value = trim(fgets(STDIN));

        shell_exec('stty echo');

        echo PHP_EOL;

        return $value;
    }
}