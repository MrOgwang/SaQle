<?php

namespace SaQle\Core\Modules;

use SaQle\App\App;
use ReflectionClass;

abstract class Module {

     final public function path(?string $append = null): string {

         $dir = dirname((new ReflectionClass($this))->getFileName());

         if(!$append){
             return $dir;
         }

         return path_join([$dir, $append]);
     }

     /**
      * Bind module services to the container
      * */
     public function register(App $app): void {

     } 

     /**
      * Declare what the module adds (admin navigation, permissions, schedules, commands, etc.)
      * */
     public function contribute(ModuleBuilder $module): void {

     }

     /**
      * Runtime initialization after the application has been built.
      * */
     public function boot(App $app): void {

     }

     /**
      * Declare module dependencies
      * */
     public function requires(): array {
         return [];
     }

     /**
     * Optional module metadata.
     */
     public function manifest(): ModuleManifest {

         return new ModuleManifest(
             name: strtolower((new ReflectionClass(static::class))->getShortName()),
             class: static::class,
             description: "Base module class",
             version: '1.0.0',
             author: "SaQle",
             homepage: "",
             priority: 1
         );

     }
}