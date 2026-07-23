<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The default authentication provider is provided so that 
 * a project can have authentication enabled as soon as the framework
 * is installed
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Core\Services\Providers;

use SaQle\Auth\Interfaces\{
     StrategyRegistryInterface,
     HashServiceInterface
};
use SaQle\Auth\Identity\Tenant\Interfaces\TenantProviderInterface;
use SaQle\Auth\Identity\User\Interfaces\UserProviderInterface;
use SaQle\Auth\Strategies\{
     PasswordLoginStrategy,
     MagicLinkLoginStrategy,
     GoogleLoginStrategy
};
use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Auth\Identity\User\Providers\PlatformUserProvider;
use SaQle\Auth\Services\PasswordHashService;

class AuthenticationProvider extends ServiceProvider {
     public function register(): void {

         //register password hashing
         $this->app->container->singleton(HashServiceInterface::class, function(){
             return new PasswordHashService();
         });

         $this->app->container->singleton(UserProviderInterface::class, function($c){
 
              return $c->resolve(config('auth.user_provider'));

         });

         //register tenant provider
         $this->app->container->singleton(TenantProviderInterface::class, function(){
             $provider = config('tenancy.tenant_provider');
             return new $provider();
         });

         //register log in strategies
         $registry = $this->app->container->resolve(StrategyRegistryInterface::class);

         $strategies = config('auth.strategies.all', ['password']);

         foreach($strategies as $s){

             $strategy_class = match($s){
                 'password' => PasswordLoginStrategy::class,
                 'google'   => GoogleLoginStrategy::class,
                 'link'     => MagicLinkLoginStrategy::class
             };

             $registry->add($s, $this->app->container->resolve($strategy_class));
         }
     }
}

