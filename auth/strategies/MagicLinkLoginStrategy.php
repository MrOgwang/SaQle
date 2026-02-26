<?php
namespace SaQle\Auth\Strategies;

use SaQle\Auth\Interfaces\LoginStrategyInterface;
use SaQle\Auth\Interfaces\UserInterface;

class MagicLinkLoginStrategy implements LoginStrategyInterface {
     public function authenticate(array $credentials): ?UserInterface {
         $token = $credentials['token'] ?? null;
         if (!$token) return null;

         //Lookup in DB
         $record = MagicLink::findValid($token);
         if (!$record) return null;

         return User::find($record->user_id);
     }
}
