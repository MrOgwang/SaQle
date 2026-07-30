<?php

namespace SaQle\Admin;

use SaQle\Auth\Context\ActorContext;

class Admin {

     protected static ?NavigationRegistry $navigation = null;

     protected static function registry(): NavigationRegistry {
         return static::$navigation ??= new NavigationRegistry();
     }

     public static function navigation(callable $callback): void {
         if(ActorContext::is_app()){
             $callback(static::registry());
         }
     }

     public static function __navigation(callable $callback): void {
         $callback(static::registry());
     }

     public static function navigation_registry(): NavigationRegistry {
         return static::registry();
     }
}