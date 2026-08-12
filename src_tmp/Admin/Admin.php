<?php

namespace SaQle\Admin;

use SaQle\Admin\Navigation\NavigationBuilder;
use SaQle\Admin\Resources\ResourceRegistry;

class Admin {

     protected static ?NavigationBuilder $appnav = null;
     protected static ?ResourceRegistry $appres = null;

     protected static function appnav_builder() : NavigationBuilder {
         return static::$appnav ??= new NavigationBuilder();
     }

     protected static function appres_builder() : ResourceRegistry {
         return static::$appres ??= new ResourceRegistry();
     }

     public static function navigation(?callable $callback = null) : NavigationBuilder {

         if($callback){
             $callback(static::appnav_builder());
         }

         return static::appnav_builder();
     }

     public static function resources(?callable $callback = null) : ResourceRegistry {
         if($callback){
             $callback(static::appres_builder());
         }

         return static::appres_builder();
     }
}