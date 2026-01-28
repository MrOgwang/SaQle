<?php

namespace SaQle\Core\Support;

use SaQle\Core\Services\IService;
use SaQle\Core\Services\Proxy\ServiceProxy;
use SaQle\Core\Events\EventBus;
use ReflectionClass;

class Controller {
	 protected function service(string $class_name){
	 	 if(is_string($class_name) && preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*$/', $class_name)){
             if(class_exists($class_name)){
                 $ref = new ReflectionClass($class_name);

			     if(!$ref->isInterface() && !$ref->isTrait()){
			         return new ServiceProxy(target: new $class_name(), event_bus: resolve(EventBus::class), request: resolve('request'));
			     }
             }
         }

         throw new RuntimeException("The class {$class_name} is not a valid service!");
	 }
}