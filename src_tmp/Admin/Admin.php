<?php

namespace SaQle\Admin;

use SaQle\Auth\Context\ActorContext;
use SaQle\Admin\Navigation\NavigationBuilder;

class Admin {

     protected static ?NavigationBuilder $navigation = null;

     /**
      * This keeps a count of how many times the builder customization
      * has been called for.
      * */
     protected static $custom_count = 0;

     protected static function builder() : NavigationBuilder {
         return static::$navigation ??= new NavigationBuilder();
     }

     public static function navigation(?callable $callback = null) : NavigationBuilder {

         if($callback){

             self::$custom_count += 1;

             /**
              * The builder is called one or more times, hence the custom_count tracking
              * 
              * Count 1: By the framework to set resource group and links
              * Count 2: By the developer to customize how they want the navigation to look like.
              * 
              * We only run the custom callback if its the framework calling builder, or if its the developer
              * calling builder in the app admin context and not the system admin context
              * */
             if(self::$custom_count === 1 || (ActorContext::is_app() && self::$custom_count === 2)){
                 $callback(static::builder());
             }
         }

         return static::builder();
     }
}