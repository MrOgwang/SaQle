<?php

namespace SaQle\Core\Support;

use SaQle\Auth\Identity\User\Interfaces\UserInterface;
use SaQle\Auth\Models\CliUser;

class ActorContext {

     private static ?UserInterface $actor = null;

     private static ?UserInterface $impersonated_actor = null;

     private static ?TenantInterface $tenant = null;

     public static function set_actor(UserInterface $actor){
         self::$actor = $actor;
     }

     public static function set_impersonated_actor(UserInterface $actor){
         self::$impersonated_actor = $actor;
     }

     public static function set_tenant(TenantInterface $tenant){
         self::$tenant = $tenant;
     }

     public static function get() : mixed {
         return self::$actor;
     }

     public static function id() : mixed {
         return self::$actor?->user_id ?? null;
     }

     public static function is_system() : bool {
         return self::$actor instanceof CliUser;
     }
}