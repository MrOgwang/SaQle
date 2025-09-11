<?php
namespace SaQle\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Orm\Entities\Model\Observer\ModelObserver;
use SaQle\Observers\Model\UserModelChangeObserver;

class RequestContextModelObserversProvider extends ServiceProvider {
     public function register(): void {
         ModelObserver::after_update(UserModelChangeObserver::class."@update_user", AUTH_MODEL_CLASS);
     }
}

