<?php
declare(strict_types=1);

namespace SaQle\Http\Request;

use SaQle\Core\Support\Session;

abstract class RuntimeContext {
     protected function __construct(
         public readonly string $type,
         public readonly array  $input = [],
         public readonly array  $errors = []
     ) {}

     protected static function session_key(string $type, string $name): string {
         return "runtime.{$type}.{$name}";
     }

     protected function persist(string $name): void {
         Session::set(
             self::session_key($this->type, $name),
             [
                 'input'  => $this->input,
                 'errors' => $this->errors
             ],
             true
         );
     }

     protected static function fetch(string $type, string $name): array {
         return Session::get(
             self::session_key($type, $name),
             [
                'input'  => [],
                'errors' => []
             ]
        );
     }

     public static function clear(string $type, string $name = ''): void {
         Session::remove(self::session_key($type, $name));
     }
}
