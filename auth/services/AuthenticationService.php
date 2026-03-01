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
 * The auth service is used to login and logout the user
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Auth\Services;

use SaQle\Auth\Interfaces\{
     UserInterface,
     StrategyRegistryInterface,
     UserProviderInterface,
     IdentityProviderResolverInterface
};
use SaQle\Auth\Utils\AuthResult;
use SaQle\Core\Services\IService;
use SaQle\Auth\Events\{LoginSucceeded, LoginFailed, Logout};
use SaQle\Core\Support\Emits;
use RuntimeException;
use Throwable;

class AuthenticationService implements IService {

     public function __construct(
         private StrategyRegistryInterface $strategies,
         private UserProviderInterface $user_provider,
         private IdentityProviderResolverInterface $identity_resolver
     ){}

     /**
     * Main login entry point.
     * $strategy_name = which login method (password, google, magic, etc.)
     */
     #[Emits(before: [LoginAttempt::class])]
     public function login(string $strategy_name, array $credentials): AuthResult {
         try{
             $strategy = $this->strategies->get($strategy_name);

             if(!$strategy){
                 throw new RuntimeException("Unknown login strategy: $strategy_name");
             }

             $user = $strategy->authenticate($credentials);

             if(!$user){
                 event(new LoginFailed($strategy_name, $credentials));
                 return new AuthResult(false, null, null, "Invalid credentials");
             }

             $identity_provider = $this->identity_resolver->resolve();

             //issue credentials
             $identity_key = $identity_provider->create($user);

             //get user id
             $user_id = $identity_provider->user_id();

             $user = $this->user_provider->find($user_id);

             //event(new LoginSucceeded($user));
             echo "Found user!";
             print_r($user);
             return new AuthResult(true, $user, $identity_key, "Login successful");
         }catch(Throwable $e){
             //log internally
             event(new LoginFailed($strategy_name, $credentials));
             return new AuthResult(false, null, null, "Authentication failed");
         }
     }

     public function resolve_user() : ?UserInterface {
         $identity_provider = $this->identity_resolver->resolve();

         $user_id = $identity_provider->user_id();

         if(!$user_id) return null;

         return $this->user_provider->find($user_id);
     }

     //#[Emits(before: [Logout::class])]
     public function logout(){

         $identity_provider = $this->identity_resolver->resolve();

         $user = $this->resolve_user();

         $identity_provider->destroy();

         return $user;
     }
}
