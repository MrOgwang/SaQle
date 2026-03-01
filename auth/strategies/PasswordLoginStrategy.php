<?php
namespace SaQle\Auth\Strategies;

use SaQle\Auth\Interfaces\LoginStrategyInterface;
use SaQle\Auth\Interfaces\UserInterface;

class PasswordLoginStrategy implements LoginStrategyInterface {
     public function authenticate(array $credentials): ?UserInterface {
         
         $username = $credentials['username'] ?? null;
         $password = $credentials['password'] ?? null;

         if (!$username || !$password) return null;

         $password = md5($password);

         $user_model = config('auth.model_class');
         $user = $user_model::get()->select(['user_id'])->where('password__eq', $password)->where('username__eq', $username)->limit(1, 1)->first_or_null();
         
         return $user;
     }
}
