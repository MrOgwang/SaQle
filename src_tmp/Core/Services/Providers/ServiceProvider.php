<?php
namespace SaQle\Core\Services\Providers;

use SaQle\App;

abstract class ServiceProvider {
      protected App $app;

      public function __construct(App $app) {
           $this->app = $app;
      }

      abstract public function register(): void;
}


