<?php

namespace SaQle\Core\Components;

use SaQle\Http\Request\Request;
use ReflectionClass;

abstract class ComponentDefinition {

     private string $path;

     public function __construct(){

         $this->path = dirname((new ReflectionClass($this))->getFileName());
     }

     final public function path(?string $append = null) : string {

         if(!$append){
             return $this->path;
         }

         return path_join([$this->path, $append]);
     }

     final public function name() : string {
         return basename($this->path);
     }

     public function template(Request $request, ...$args): ?string {
         return null;
     }
}