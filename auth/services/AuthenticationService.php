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

use SaQle\Auth\Interfaces\StrategyRegistryInterface;
use SaQle\Auth\Identity\User\Interfaces\{
     UserIDResolverInterface,
     UserInterface,
     UserProviderInterface
};
use SaQle\Auth\Identity\User\Factories\UserIDResolverFactory;
use SaQle\Auth\Utils\AuthResult;
use SaQle\Core\Services\IService;
use SaQle\Auth\Events\{LoginSucceeded, LoginFailed, Logout};
use SaQle\Core\Support\Emits;
use RuntimeException;
use Throwable;

class AuthenticationService implements IService {

     private UserIDResolverInterface $id_resolver;

     public function __construct(
         private StrategyRegistryInterface $strategies,
         private UserProviderInterface $user_provider
     ){
         $this->id_resolver = UserIDResolverFactory::make();
     }

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

             //issue credentials
             $identity_token = $this->id_resolver->create($user);

             //get user id
             $user_id = $this->id_resolver->resolve();

             $user = $this->user_provider->find($user_id);

             //event(new LoginSucceeded($user));
             return new AuthResult(true, $user, $identity_token, "Login successful");
         }catch(Throwable $e){
             //log internally
             event(new LoginFailed($strategy_name, $credentials));
             return new AuthResult(false, null, null, "Authentication failed");
         }
     }

     public function resolve_user() : ?UserInterface {
         $user_id = $this->id_resolver->resolve();

         if(!$user_id) return null;

         return $this->user_provider->find($user_id);
     }

     //#[Emits(before: [Logout::class])]
     public function logout(){

         $user = $this->resolve_user();

         $this->id_resolver->destroy();

         $identity_provider->destroy();

         return $user;
     }
}
