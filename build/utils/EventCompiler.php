<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Events\EventDiscoverer;
use SaQle\Core\Registries\EventRegistry;

final class EventCompiler {

     public static function compile() {
         //get listener directories
         $listener_dirs = [DOCUMENT_ROOT.'/listeners'];

         foreach(INSTALLED_APPS as $app){
             $listener_dirs[] = DOCUMENT_ROOT.'/apps/'.$app.'/listeners';
         }

         //Discover attributed listeners
         $registry = resolve(EventRegistry::class); // Gets CachedEventRegistry in prod context
         $discoverer = new EventDiscoverer($listener_dirs);
         $discoverer->discover_and_register($registry);

         //Finally, save the full registry (explicit + discovered) to cache
         $registry->save_to_cache();
     }
}
