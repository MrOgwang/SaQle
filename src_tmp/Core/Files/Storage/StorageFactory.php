<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\Storage\Drivers\LocalStorageDriver;
use RuntimeException;

final class StorageFactory {

     public static function make(string $name): Storage {

         $driver_class = self::get_driver($name);

         $config = config('app.media_storage_drivers', [])[$name] ?? null;

         if(!$config){
             throw new RuntimeException("The storage driver: {$name} not valid!");
         }

         return new Storage(new $driver_class($config));
     }

     public static function get_driver(string $name){
         return match($name){
             'local' => LocalStorageDriver::class,
             default => throw new RuntimeException("The storage driver: {$name} not valid!")
         };
     }
}
