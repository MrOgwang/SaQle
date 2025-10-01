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

use SaQle\Auth\Utils\{AuthManager, AuthResult};
use SaQle\Core\Assert\Exceptions\InvalidArgumentException;
use SaQle\Auth\Models\Interfaces\IUser;
use SaQle\Core\Services\IService;
use SaQle\Core\Services\Attr\ResultName;

class AuthService implements IService {
     /**
     * Main login entry point.
     * $strategy_name = which login method (password, google, magic, etc.)
     */
     #[ResultName(name: 'auth_result')]
     public function login(string $strategy_name, array $credentials): AuthResult {
         $strategy = AuthManager::get_strategy($strategy_name);

         if(!$strategy) throw new InvalidArgumentException("Unknown login strategy: $strategy_name!");

         $user = $strategy->authenticate($credentials);

         if(!$user) return new AuthResult(false, null, null, "Invalid credentials");

         $provider_resolver = AuthManager::get_session_provider_resolver();
         $provider = $provider_resolver();

         //issue credentials
         $session_key = $provider->create_session($user);

         //get user id
         $id = $provider->get_user_id();

         $user_provider = AuthManager::get_user_provider();

         if(!$user_provider) throw new \RuntimeException("No UserProvider registered.");

         return new AuthResult(true, $user_provider($id), $session_key, "Login successful");
     }

     public function get_current_user() : ?IUser {
         $provider_resolver = AuthManager::get_session_provider_resolver();
         $provider = $provider_resolver();

         $id = $provider->get_user_id();
         if(!$id) return null;

         $user_provider = AuthManager::get_user_provider();

         if(!$user_provider) throw new \RuntimeException("No UserProvider registered.");

         return $user_provider($id);
     }
}
