<?php
namespace SaQle\Auth\Strategies;

use SaQle\Auth\Strategies\Interfaces\LoginStrategy;
use SaQle\Auth\Models\Interfaces\IUser;

class PasswordLoginStrategy implements LoginStrategy {
     public function authenticate(array $credentials): ?IUser {
         $username = $credentials['username'] ?? null;
         $password = $credentials['password'] ?? null;

         if (!$username || !$password) return null;

         $password = md5($password);

         $user_model = AUTH_MODEL_CLASS;
         return $user_model::get()->select(['user_id'])->where('password__eq', $password)->where('username__eq', $username)->limit(1, 1)->first_or_default();

         return $user;
     }
}
