<?php

namespace SaQle\Core\Data;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use SaQle\Core\Exceptions\Data\KeyNotFoundException;

class Data implements ArrayAccess, Countable, IteratorAggregate {

     protected array $data = [];

     public function __construct(array $data = []) {
         $this->data = $data;
     }

     public function set(string $key, mixed $value): static {
         
         $this->data[$key] = $value;

         return $this;
     }

     public function get(string $key, mixed $default = null): mixed {
         
         return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
     }

     public function get_or_fail(string $key): mixed {

         if(!$this->exists($key)) {
             throw new KeyNotFoundException(
                 context: [
                     'type' => static::class,
                     'key'  => $key
                 ]
             );
         }

         return $this->data[$key];
     }

     public function exists(string $key): bool {
         return array_key_exists($key, $this->data);
     }

     public function remove(string $key): static {
         
         unset($this->data[$key]);

         return $this;
     }

     public function clear(): static {
         
         $this->data = [];

         return $this;
     }

     public function get_all(): array {
         return $this->data;
     }

     public function keys(): array {
         return array_keys($this->data);
     }

     public function count(): int {
         return count($this->data);
     }

     public function is_empty(): bool {
         return empty($this->data);
     }

     public function merge(array $data): static {
         
         $this->data = array_merge($this->data, $data);

         return $this;
     }

     public function replace(array $data): static {
         
         $this->data = $data;

         return $this;
     }

     public function only(array $keys): array {
        
         return array_intersect_key($this->data, array_flip($keys));
     }

     public function except(array $keys): array {

         return array_diff_key($this->data, array_flip($keys));
     }

     public function getIterator(): Traversable {
         return new ArrayIterator($this->data);
     }

     public function offsetExists(mixed $offset): bool {
         return $this->exists($offset);
     }

     public function offsetGet(mixed $offset): mixed {
         return $this->get($offset);
     }

     public function offsetSet(mixed $offset, mixed $value): void {
         $this->set($offset, $value);
     }

     public function offsetUnset(mixed $offset): void {
         $this->remove($offset);
     }
}