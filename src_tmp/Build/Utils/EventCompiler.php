<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Events\EventDiscoverer;
use SaQle\Core\Registries\EventRegistry;

final class EventCompiler {

     public static function compile() {
         $base_path = config('base_path');

         //get listener directories
         $listener_dirs = [path_join([$base_path, 'listeners'])];

         foreach(config('app.modules') as $app){
             $listener_dirs[] = path_join([$base_path, 'apps', $app, 'listeners']);
         }

         //Discover attributed listeners
         $registry = resolve(EventRegistry::class); // Gets CachedEventRegistry in prod context
         $discoverer = new EventDiscoverer($listener_dirs);
         $discoverer->discover_and_register($registry);

         //Finally, save the full registry (explicit + discovered) to cache
         $registry->save_to_cache();
     }
}
