<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\Storage\Drivers\LocalStorageDriver;
use RuntimeException;

final class StorageFactory {

     public static function make(string $name): Storage {
         $config = config('media_storage_drivers')[$name] ?? null;

         //Framework default
         if(!$config){
             $config = [
                 'driver' => LocalStorageDriver::class,
                 'root' => base_path('storage/uploads'),
                 'visibility' => 'private',
                 'base_url' => null,
             ];
         }

         $driver_class = $config['driver'];

         return new Storage(new $driver_class($config));
     }
}
