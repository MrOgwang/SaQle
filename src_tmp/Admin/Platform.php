<?php

namespace SaQle\Admin;

use SaQle\Admin\Navigation\NavigationBuilder;
use SaQle\Admin\Resources\ResourceRegistry;

class Platform {

     protected static ?NavigationBuilder $systemnav = null;
     protected static ?ResourceRegistry $systemres = null;

     protected static function systemnav_builder() : NavigationBuilder {
         return static::$systemnav ??= new NavigationBuilder();
     }

     protected static function systemres_builder() : ResourceRegistry {
         return static::$systemres ??= new ResourceRegistry();
     }

     public static function navigation(?callable $callback = null) : NavigationBuilder {

         if($callback){
             $callback(static::systemnav_builder());
         }

         return static::systemnav_builder();
     }

     public static function resources(?callable $callback = null) : ResourceRegistry {
         if($callback){
             $callback(static::systemres_builder());
         }

         return static::systemres_builder();
     }
}