<?php
namespace SaQle\Auth\Strategies;

use SaQle\Auth\Strategies\Interfaces\LoginStrategy;
use SaQle\Auth\Models\Interfaces\IUser;

class MagicLinkLoginStrategy implements LoginStrategy {
     public function authenticate(array $credentials): ?IUser {
         $token = $credentials['token'] ?? null;
         if (!$token) return null;

         //Lookup in DB
         $record = MagicLink::findValid($token);
         if (!$record) return null;

         return User::find($record->user_id);
     }
}
