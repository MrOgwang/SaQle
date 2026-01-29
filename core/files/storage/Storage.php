<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\Storage\Drivers\IStorageDriver;

class Storage {
     public function __construct(
         protected IStorageDriver $driver
     ){}

     public function put(string $path, mixed $contents) : void {
         $this->driver->put($path, $contents);
     }

     public function url(string $path) : ?string {
         return $this->driver->url($path);
     }

     public function path(string $path) : string {
         return $this->driver->path($path);
     }

     public function exists(string $path) : bool {
         return $this->driver->exists($path);
     }

     public function delete(string $path) : void {
         $this->driver->delete($path);
     }

     public function read(string $path) : mixed {
         return $this->driver->read($path);
     }
}
