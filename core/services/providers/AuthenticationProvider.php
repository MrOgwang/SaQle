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
     UserProviderInterface,
     IdentityProviderResolverInterface
};
use SaQle\Auth\Identity\Providers\{
     DefaultUserProvider
};
use SaQle\Auth\Identity\Resolvers\{
     DefaultIdentityProviderResolver
};
use SaQle\Core\Registries\LoginStrategyRegistry;
use SaQle\Auth\Strategies\PasswordLoginStrategy;
use SaQle\Core\Services\Providers\ServiceProvider;

class AuthenticationProvider extends ServiceProvider {
     public function register(): void {
         $this->app->container->singleton(
             StrategyRegistryInterface::class,
             LoginStrategyRegistry::class
         );

         /**
          * Register the user provider. This provides the user object 
          * to be injected into the request. This allows you the chance
          * to define how the session user is fetched and the kind of properties you 
          * want in the user object
          * */
         $this->app->container->singleton(
             UserProviderInterface::class,
             fn() => new DefaultUserProvider(config('auth.model_class'))
         );


         /**
          * Register identity provider resolver. This is a callback that determines
          * whether the request uses php sessions or jwt tokens to store and track 
          * session.
          * */
         $this->app->container->singleton(
             IdentityProviderResolverInterface::class,
             DefaultIdentityProviderResolver::class
         );

         /**
          * Register available login strategies. Login strategies are the several ways you
          * want to login users into the application. You will choose the strategy to use 
          * depending on your specific needs and circumstances
          * */
         $registry = $this->app->container->resolve(StrategyRegistryInterface::class);

         $registry->add('password', $this->app->container->resolve(PasswordLoginStrategy::class));
     }
}

