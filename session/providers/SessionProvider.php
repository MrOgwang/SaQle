<?php

namespace SaQle\Session\Providers;

use SaQle\Core\Config\Config;
use SaQle\Core\Services\Providers\ServiceProvider;

final class SessionProvider extends ServiceProvider {
     public function register(): void {
         //Apply session handler if configured
         $handler_class = SESSION_HANDLER;
         if($handler_class){
             session_set_save_handler(new $handler_class(), true);
         }

         ini_set('session.cookie_domain', COOKIE_DOMAIN);
         ini_set('session.gc_maxlifetime', SESSION_GC_MAXLIFETIME);
         ini_set('session.cookie_lifetime', SESSION_COOKIE_LIFETIME);
         ini_set('session.gc_probability', SESSION_GC_PROBABILITY);
         ini_set('session.gc_divisor', SESSION_GC_DIVISOR);
     }
}
