<?php

namespace SaQle\Auth\Context;

use SaQle\Auth\Identity\User\Interfaces\UserInterface;
use SaQle\Auth\Models\CliUser;

class ActorContext {

     private static ?UserInterface $_user = null;

     private static ?UserInterface $_impersonated_user = null;

     private static ?TenantInterface $_tenant = null;

     private static Realm $_realm = Realm::APP;

     //realm methods
     public static function to_platform(): void {
         self::$_realm = Realm::PLATFORM;
     }

     public static function to_app(): void {
         self::$_realm = Realm::APP;
     }

     public static function realm(): Realm {
         return self::$_realm;
     }

     public static function is_platform(): bool {
         return self::$_realm === Realm::PLATFORM;
     }

     public static function is_app(): bool {
         return self::$_realm === Realm::APP;
     }

     //actor methods

     public static function set_user(UserInterface $user){
         self::$_user = $user;
     }

     public function set_impersonated_user(UserInterface $user){
         self::$_impersonated_user = $user;
     }

     public static function actor(){
         if(self::$_impersonated_user){
             return self::$_impersonated_user;
         }

         return self::$_user;
     }
    
     //tenant methods

     public static function set_tenant(TenantInterface $tenant){
         self::$_tenant = $tenant;
     }

     public static function tenant() : ?TenantInterface {
         return self::$_tenant;
     }

     public static function is_system() : bool {
         return self::$_user instanceof CliUser;
     }
}