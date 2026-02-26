<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Connection\Connection;
use SaQle\Http\Request\Request;
use SaQle\Log\FileLogger;
use SaQle\Core\Registries\EventRegistry;
use SaQle\Core\Events\EventBus;
use SaQle\Routes\Canonical\CanonicalUrlPolicy;
use SaQle\Routes\Canonical\TrailingSlashPolicy;

class FrameworkDIProvider extends ServiceProvider {
     public function register(): void {
         $this->app->container->singleton(Request::class, function($c){
             return Request::init();
         });
         $this->app->container->singleton('request', function($c){
             return Request::init();
         });
         $this->app->container->bind(ConnectionConfig::class, function($c, ...$connection_params){
             return new ConnectionConfig(...$connection_params);
         });
         $this->app->container->bind(Connection::class, function($c, ...$connection_params){
             return Connection::make($c->resolve(ConnectionConfig::class, $connection_params));
         });
         $this->app->container->bind(FileLogger::class, function($c, $path, $mode){
             return new FileLogger(file_path: $path, file_mode: $mode);
         });
         $this->app->container->bind(EventRegistry::class, fn () => $this->app->events);
         $this->app->container->bind(EventBus::class, fn () =>
             new EventBus($this->app->container->resolve(EventRegistry::class))
         );
         $this->app->container->bind(CanonicalUrlPolicy::class, function(){
             return new TrailingSlashPolicy();
         });
     }
}

