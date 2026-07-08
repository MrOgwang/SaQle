<?php

namespace SaQle\Core\Support;

use ReflectionClass;
use RuntimeException;

class Controller {
	 protected function service(string $class_name){
	 	 if(is_string($class_name) && preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*$/', $class_name)){
             if(class_exists($class_name)){
                 $ref = new ReflectionClass($class_name);

			     if(!$ref->isInterface() && !$ref->isTrait()){
			     	 return resolve($class_name);
			     }
             }
         }

         throw new RuntimeException("The class {$class_name} is not a valid service!");
	 }
}