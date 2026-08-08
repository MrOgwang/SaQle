<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Events\EventDiscoverer;
use SaQle\Core\Registries\EventRegistry;
use SaQle\Core\Support\Cli;

final class EventCompiler {

     public static function compile(){

         Cli::print("Compiling events and listeners...");

         $base_path = config('base_path');

         //get listener directories
         $listener_dirs = [path_join([$base_path, 'listeners'])];

         foreach(config('app.modules') as $m){
             $listener_dirs[] = (new $m())->path('Listeners');
         }

         //Discover attributed listeners
         $registry = resolve(EventRegistry::class); // Gets CachedEventRegistry in prod context
         $discoverer = new EventDiscoverer($listener_dirs);
         $discoverer->discover_and_register($registry);

         //Finally, save the full registry (explicit + discovered) to cache
         $registry->save_to_cache();

         Cli::print("Events/listeners compiled and cached\n");
     }
}
