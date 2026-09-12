<?php

namespace SaQle\Console;

class Cli {
     public function line(string $text = ''): void {
         fwrite(STDOUT, $text.PHP_EOL);
     }

     public function success(string $text): void {
         fwrite(STDOUT, "\033[32m{$text}\033[0m".PHP_EOL);
     }

     public function error(string $text): void {
         fwrite(STDERR, "\033[31m{$text}\033[0m".PHP_EOL);
     }

     public function warning(string $text): void {
         fwrite(STDOUT, "\033[33m{$text}\033[0m".PHP_EOL);
     }

     public function info(string $text): void {
         fwrite(STDOUT, "\033[36m{$text}\033[0m".PHP_EOL);
     }
}