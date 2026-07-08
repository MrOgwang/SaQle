<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Core\Files\Storage\Storage;

class StorageServiceProvider extends ServiceProvider {
     public function register(): void {

         $config = config('app.media_storage_drivers');

         foreach($config as $name => $storage){
             $driver_class = $storage['driver'];

             $driver = new $driver_class($storage);

             $this->app->disks->add($name, new Storage($driver));
         }
     }
}

