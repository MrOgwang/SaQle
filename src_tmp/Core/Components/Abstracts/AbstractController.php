<?php

namespace SaQle\Core\Components\Abstracts;

use ReflectionClass;
use RuntimeException;
use SaQle\Core\Components\{
	 Context,
	 Component
};

abstract class AbstractController {

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

	 final protected function context() : Context {
	 	 return Component::current()->context();
	 } 
}