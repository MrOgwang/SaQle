<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\Storage\Drivers\LocalStorageDriver;
use SaQle\Core\Files\Utils\DefaultFileUrlEncoder;
use RuntimeException;

final class StorageFactory {

     public static function make(string $name): Storage {
         $config = config('app.media_storage_drivers')[$name] ?? null;

         //Framework default
         if(!$config){
             $config = [
                 'driver' => LocalStorageDriver::class,
                 'root' => media_root('media', false),
                 'visibility' => 'private',
                 'base_url' => '/media',
                 'url_encoder' => DefaultFileUrlEncoder::class
             ];
         }

         $driver_class = $config['driver'];

         return new Storage(new $driver_class($config));
     }
}
