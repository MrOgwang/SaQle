<?php
namespace SaQle\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use Booibo\Apps\Fan\Services\SpaceService;
use SaQle\Core\Services\Observer\ServiceObserver;
use SaQle\Apps\Fan\Observers\{SpaceInvitationNotificationObserver, SpaceInvitationEmailObserver, SpaceInvitationLoggerObserver};

use SaQle\Auth\Observers\{SigninObserver, SignoutObserver};

class DefaultServiceObserverProvider extends ServiceProvider {
     public function register(): void {
         ServiceObserver::after([SigninObserver::class], AUTH_BACKEND_CLASS, 'authenticate');
         ServiceObserver::after([SignoutObserver::class], AUTH_BACKEND_CLASS, 'signout');
     }
}

