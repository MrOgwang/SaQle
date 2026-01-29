<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Core\Files\Storage\Storage;

class StorageServiceProvider extends ServiceProvider {
     public function register(): void {

         $config = config('media_storage_drivers');

         foreach($config as $name => $storage){
             $driver_class = $storage['driver'];

             $driver = new $driver_class(
                ...array_values(array_diff_key($storage, ['driver' => true]))
             );

             $this->app->disks->add($name, new Storage($driver));
         }
     }
}

