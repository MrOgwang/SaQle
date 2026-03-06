<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\Storage\Drivers\LocalStorageDriver;
use SaQle\Core\Files\Generators\DefaultPrivateFileUrlGenerator;
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
                 'private_url_generator' => DefaultPrivateFileUrlGenerator::class
             ];
         }

         $driver_class = $config['driver'];

         return new Storage(new $driver_class($config));
     }
}
