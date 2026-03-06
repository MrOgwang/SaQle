<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\Storage\Drivers\IStorageDriver;
use SaQle\Core\Files\Generators\DefaultPrivateFileUrlGenerator;

class Storage {
     public function __construct(
         protected IStorageDriver $driver
     ){}

     public function put(string $path, mixed $contents) : void {
         $this->driver->put($path, $contents);
     }

     public function url(array $file_meta) : ? string {
         $path = path_join([$file_meta['path'], $file_meta['name']]);
         
         $visibility = $this->driver->config()['visibility'] ?? 'private';

         if($visibility === 'public'){
             return $this->driver->public_url($path);
         }

         $generator_class = $this->driver->config()['private_url_generator']
            ?? DefaultPrivateFileUrlGenerator::class;

         $generator = new $generator_class($this->driver);

         return $generator->generate($file_meta);
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
