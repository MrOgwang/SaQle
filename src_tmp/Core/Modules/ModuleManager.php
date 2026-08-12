<?php

namespace SaQle\Core\Modules;

use SaQle\App\App;
use InvalidArgumentException;

final class ModuleManager {

     private array $modules = [];

     public function __construct(
         private App $app
     ){}

     public function load(array $module_classes): void {

         foreach($module_classes as $class){

             $module = new $class();

             if(!$module instanceof Module){
                 throw new InvalidArgumentException("{$class} must extend ".Module::class);
             }

             $this->modules[$class] = $module;
         }

         $this->resolve_dependencies();
     }

     public function register() : void {
         foreach($this->modules as $class => $instance){
             $instance->register($this->app);
         } 
     }

     public function contribute() : void {
         foreach($this->modules as $class => $instance){
            
             $builder = new ModuleBuilder($instance, $this->app);

             $instance->contribute($builder);
         }
     }

     public function boot(): void {
         foreach($this->modules as $class => $instance){

             $instance->boot($this->app);
         }
     }

     public function all(): array {
         return $this->modules;
     }

     private function resolve_dependencies(): void {
         // Dependency resolution goes here.
     }
}