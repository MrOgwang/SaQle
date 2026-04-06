<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\Storage\Drivers\IStorageDriver;
use SaQle\Core\Files\Utils\DefaultFileUrlEncoder;

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

         $encoder_class = $this->driver->config()['url_encoder'] ?? DefaultFileUrlEncoder::class;

         $encoder = new $encoder_class($this->driver);

         $token = $encoder->encode($file_meta);

         $route_name = "app.".$file_meta['storage'].".media";

         return route($route_name, [
             'file' => rawurlencode($token),
             'storage_key' => $file_meta['storage']
         ]);
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
