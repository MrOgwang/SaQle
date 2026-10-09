<?php

namespace SaQle\Core\Components\Abstracts;

use SaQle\Http\Request\Request;
use SaQle\Core\Components\{
     Context,
     Component
};
use ReflectionClass;

abstract class AbstractDefinition {

     //the path to the component folder
     private string $path;

     public function __construct(array $props = []){

         $this->path = dirname((new ReflectionClass($this))->getFileName());

         $this->initialize($props);
     }

     protected function initialize(array $props = []) : void {
         foreach($props as $key => $value){
             $this->context()->props()->set($key, $value);
         }
     }

     /**
      * Return the component path.
      * */
     final public function path(?string $append = null) : string {

         if(!$append){
             return $this->path;
         }

         return path_join([$this->path, $append]);
     }

     /**
      * Return the name of the component
      * */
     final public function name() : string {
         return basename($this->path);
     }

     /**
      * If a component has multiple template variations,
      * return which to display
      * */
     public function template(Request $request): ?string {
         return null;
     }

     /**
      * Return scripts and styles dependencies
      * */
     public function dependencies() : array {
         return [];
     }

     /**
      * Configure how component routes will work
      * */
     public function routes() : array {
         return [];
     }

     /**
      * Return a desired component theme
      * */
     public function theme() : string {
         return "Default";
     }

     final protected function context() : Context {
         return Component::current()->context();
     } 
}