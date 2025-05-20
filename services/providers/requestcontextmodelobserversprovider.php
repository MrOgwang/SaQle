<?php
namespace SaQle\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Orm\Entities\Model\Observer\ModelObserver;
use SaQle\Observers\Model\RequestContextModelUpdateObserver;

class RequestContextModelObserversProvider extends ServiceProvider {
     public function register(): void {
         ModelObserver::after_update(RequestContextModelUpdateObserver::class);
     }
}

