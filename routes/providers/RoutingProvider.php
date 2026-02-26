<?php

namespace SaQle\Routes\Providers;

use SaQle\Core\Config\Config;
use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Routes\Router;

final class RoutingProvider extends ServiceProvider{
     public function register(): void {
         //add media url routing
         Router::get(config('app.media_url'), 'mediacontroller');
     }
}
