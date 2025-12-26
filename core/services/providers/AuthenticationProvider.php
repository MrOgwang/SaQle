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

use SaQle\Auth\Utils\AuthManager;
use SaQle\Auth\Strategies\PasswordLoginStrategy;
use SaQle\Auth\Providers\Resolver\DefaultProviderResolver;
use SaQle\Core\Services\Providers\ServiceProvider;

class AuthenticationProvider extends ServiceProvider {
     public function register(): void {
         /**
          * Register available login strategies. Login strategies are the several ways you
          * want to login users into the application. You will choose the strategy to use 
          * depending on your specific needs and circumstances
          * */
         AuthManager::add_strategy('password', new PasswordLoginStrategy());

         /**
          * Register the user provider. This is a callback that will be used
          * to fetch the user and inject it into the request. This allows you the chance
          * to define how the session user is fetched and the kind of properties you 
          * want in the user object
          * */
         AuthManager::set_user_provider(function(string|int $id){
             //get and return the user here
             $model = AUTH_MODEL_CLASS;
             return $model::get()->where('user_id', $id)->first();
         });

         /**
          * Register session provider resolver. This is a callback that determines
          * whether the request uses php sessions or jwt tokens to store and track 
          * session.
          * */
         AuthManager::set_session_provider_resolver([new DefaultProviderResolver(), 'resolve_provider']);
     }
}

