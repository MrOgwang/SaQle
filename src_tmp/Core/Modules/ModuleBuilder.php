<?php

namespace SaQle\Core\Modules;

use SaQle\App\App;
use RuntimeException;

final class ModuleBuilder {

     public function __construct(
     	 private Module $module,
         private App $app
     ){
         $this->routes();
     }

     public function path(): string {
         return $this->module->path();
     }

     public function routes(): RouteModuleBuilder {
         return new RouteModuleBuilder($this->module);
     }

     public function events(): EventModuleBuilder {
         return new EventModuleBuilder($this->module);
     }

     public function listeners(): ListenerModuleBuilder {
         return new ListenerModuleBuilder($this->module);
     }

     public function notifications(): NotificationModuleBuilder {
         return new NotificationModuleBuilder($this->module);
     }

     public function commands(): CommandModuleBuilder {
         return new CommandModuleBuilder($this->module);
     }

     public function admin(): AdminModuleBuilder {

     	 if(!$this->module instanceof AdminModule){
     	 	 throw new RuntimeException('This is not an admin module!');
     	 }

         return new AdminModuleBuilder($this->module);
     }

     public function platform(): PlatformModuleBuilder {

         if(!$this->module instanceof AdminModule){
             throw new RuntimeException('This is not an admin module!');
         }

         return new PlatformModuleBuilder($this->module);
     }
}