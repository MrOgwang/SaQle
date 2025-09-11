<?php

namespace SaQle\Auth\utils;

use SaQle\Auth\Strategies\PasswordLoginStrategy;
use SaQle\Auth\Strategies\Interfaces\LoginStrategy;
use Closure;

class AuthManager {
     private static ?AuthManager $manager = null;

     /** @var array<string, LoginStrategy> */
     protected static array $strategies = [];

     protected static $user_provider;

     protected static $session_provider_resolver;

     private function __construct(){}

     public static function add_strategy(string $name, LoginStrategy $strategy): void {
         self::$strategies[$name] = $strategy;
     }

     public static function get_strategy(string $name): ?LoginStrategy {
         return self::$strategies[$name] ?? null;
     }

     public static function all_strategies(): array{
         return self::$strategies;
     }

     public static function set_user_provider(callable $user_provider){
         self::$user_provider = $user_provider;
     }

     public static function get_user_provider(){
         return self::$user_provider;
     }

     public static function set_session_provider_resolver(callable $session_provider_resolver){
        self::$session_provider_resolver = $session_provider_resolver;
     }

     public static function get_session_provider_resolver(){
         return self::$session_provider_resolver;
     }
}
