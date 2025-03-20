<?php
namespace SaQle\Core\Services\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use Exception;
use ReflectionFunctionAbstract;

class Container {
     private static ?Container $instance = null;
     private array $bindings = [];
     private array $instances = [];

     private function __construct(){}

     public static function init(): Container {
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     //bind a class or interface to the container
     public function bind(string $abstract, callable|string|null $concrete = null, bool $singleton = false): void {
         $this->bindings[$abstract] = [
             'concrete' => $concrete ?? $abstract,
             'singleton' => $singleton
         ];
     }

     //bind a singleton instance to the container
     public function singleton(string $abstract, callable|string|null $concrete = null): void {
         $this->bind($abstract, $concrete, true);
     }

     //resolve a class from the container
     public function resolve(string $abstract, array $parameters = []) {
         if(isset($this->instances[$abstract])){
             return $this->instances[$abstract];
         }

         $concrete = $this->bindings[$abstract]['concrete'] ?? $abstract;
         $object = ($concrete instanceof Closure) ? $concrete($this, ...$parameters) : $this->build($concrete, $parameters);

         if($this->bindings[$abstract]['singleton'] ?? false) {
             $this->instances[$abstract] = $object;
         }

         return $object;
     }

     //build a class and resolve its dependencies
     private function build(string $concrete, array $parameters = []) {
         if(!class_exists($concrete)){
             throw new Exception("Cannot resolve {$concrete}: Class does not exist.");
         }

         $reflection = new ReflectionClass($concrete);
         if(!$constructor = $reflection->getConstructor()){
             return new $concrete;
         }

         $dependencies = $this->resolve_dependencies($constructor, $parameters);
         return $reflection->newInstanceArgs($dependencies);
     }

     //resolve method parameters (constructor or other methods)
     private function resolve_dependencies(ReflectionFunctionAbstract $method, array $parameters = []): array {
         $dependencies = [];

         foreach ($method->getParameters() as $param){
             $paramType = $param->getType();

             if($paramType && !$paramType->isBuiltin()){
                 $dependencies[] = $this->resolve($paramType->getName());
             }elseif(array_key_exists($param->getName(), $parameters)){
                 $dependencies[] = $parameters[$param->getName()];
             }elseif ($param->isDefaultValueAvailable()) {
                 $dependencies[] = $param->getDefaultValue();
             }else{
                 throw new Exception("Cannot resolve parameter {$param->getName()}.");
             }
         }

         return $dependencies;
     }

     //rsolve and call a method with dependencies
     public function call_method(object|string $class, string $method, array $parameters = []){
         $object = is_object($class) ? $class : $this->resolve($class);
         $reflectionMethod = new ReflectionMethod($object, $method);
         $dependencies = $this->resolve_dependencies($reflectionMethod, $parameters);

         return $reflectionMethod->invokeArgs($object, $dependencies);
     }
}
?>
