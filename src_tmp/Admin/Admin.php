<?php

namespace SaQle\Admin;

use SaQle\Auth\Context\ActorContext;
use SaQle\Admin\Navigation\NavigationBuilder;
use SaQle\Admin\Resources\ResourceRegistry;

class Admin {

     protected static ?NavigationBuilder $navigation = null;

     protected static ?ResourceRegistry $resources = null;

     /**
      * This keeps a count of how many times the navigation builder customization
      * has been called for.
      * */
     protected static $navigation_track = 0;

     /**
      * This keeps a count of how many times the resources builder customization
      * has been called for.
      * */
     protected static $resource_track = 0;

     protected static function navigation_builder() : NavigationBuilder {
         return static::$navigation ??= new NavigationBuilder();
     }

     protected static function resource_builder() : ResourceRegistry {
         return static::$resources ??= new ResourceRegistry();
     }

     public static function navigation(?callable $callback = null) : NavigationBuilder {

         if($callback){

             self::$navigation_track += 1;

             /**
              * The navigation builder is called one or more times, hence the navigation_track tracking
              * 
              * Count 1: By the framework to set resource group and links
              * Count 2: By the developer to customize how they want the navigation to look like.
              * 
              * We only run the custom callback if its the framework calling builder, or if its the developer
              * calling builder in the app admin context and not the system admin context
              * */
             if(self::$navigation_track === 1 || (ActorContext::is_app() && self::$navigation_track === 2)){
                 $callback(static::navigation_builder());
             }
         }

         return static::navigation_builder();
     }

     public static function resources(?callable $callback = null) : ResourceRegistry {
         if($callback){

             self::$resource_track += 1;

             /**
              * The navigation builder is called one or more times, hence the navigation_track tracking
              * 
              * Count 1: By the framework to set resource group and links
              * Count 2: By the developer to customize how they want the navigation to look like.
              * 
              * We only run the custom callback if its the framework calling builder, or if its the developer
              * calling builder in the app admin context and not the system admin context
              * */
             if(self::$resource_track === 1 || (ActorContext::is_app() && self::$resource_track === 2)){
                 $callback(static::resource_builder());
             }
         }

         return static::resource_builder();
     }
}