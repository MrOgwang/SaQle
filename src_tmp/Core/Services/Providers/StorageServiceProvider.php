<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Core\Files\Storage\{
     Storage,
     StorageFactory
};

class StorageServiceProvider extends ServiceProvider {

     public function register(): void {

         $storage_drivers = config('app.media_storage_drivers');

         foreach($storage_drivers as $name => $config){
             
             $driver_class = StorageFactory::get_driver($name);

             $driver = new $driver_class($config);

             $this->app->disks->add($name, new Storage($driver));
         }
     }
}

