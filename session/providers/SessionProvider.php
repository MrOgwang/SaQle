<?php

namespace SaQle\Session\Providers;

use SaQle\Core\Config\Config;
use SaQle\Core\Services\Providers\ServiceProvider;

final class SessionProvider extends ServiceProvider {
     public function register(): void {
         //Apply session handler if configured
         $handler_class = config('session_handler');
         if($handler_class){
             session_set_save_handler(new $handler_class(), true);
         }

         ini_set('session.cookie_domain', config('cookie_domain'));
         ini_set('session.gc_maxlifetime', config('session_gc_maxlifetime'));
         ini_set('session.cookie_lifetime', config('session_cookie_lifetime'));
         ini_set('session.gc_probability', config('session_gc_probability'));
         ini_set('session.gc_divisor', config('session_gc_divisor'));
     }
}
