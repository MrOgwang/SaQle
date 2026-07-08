<?php

namespace SaQle\Core\Support;

use ReflectionClass;
use ReflectionMethod;

class AttributeResolver {
     private array $cache = [];

     public function get_class_attributes(
         string|object $class, 
         string $attribute_class, 
         bool $inherit = true
     ) : array {
         $class_name = is_object($class) ? $class::class : $class;

         $cache_key = "class|{$class_name}|{$attribute_class}|" . ($inherit ? '1' : '0');

         if(isset($this->cache[$cache_key])){
             return $this->cache[$cache_key];
         }

         $result = [];
         $ref = new ReflectionClass($class_name);

         while($ref){
             $attrs = $ref->getAttributes($attribute_class);
             foreach($attrs as $attr){
                 $instance = $attr->newInstance();
                 $result[] = $instance;

                 //stop inheritance chain if explicitly non-inheritable
                 if(property_exists($instance, 'inheritable') && $instance->inheritable === false){
                     return $this->cache[$cache_key] = $result;
                 }
             }

             if(!$inherit){
                 break;
             }

             $ref = $ref->getParentClass();
         }

         return $this->cache[$cache_key] = $result;
     }

     public function get_effective_class_attribute(
         string|object $class,
         string $attribute_class,
         bool $inherit = true
     ): ?object {
         return $this->get_class_attributes($class, $attribute_class, $inherit)[0] ?? null;
     }

     public function get_method_attributes(
         string|object $class,
         string $method,
         string $attribute_class
     ) : array {
         $class_name = is_object($class) ? $class::class : $class;

         $cache_key = "method|{$class_name}|{$method}|{$attribute_class}";

         if(isset($this->cache[$cache_key])){
             return $this->cache[$cache_key];
         }

         $result = [];
         $ref = new ReflectionMethod($class_name, $method);

         foreach ($ref->getAttributes($attribute_class) as $attr){
             $result[] = $attr->newInstance();
         }

         return $this->cache[$cache_key] = $result;
     }

     public function get_effective_method_attribute(
         string|object $class,
         string $method,
         string $attribute_class
     ) : ?object {
        return $this->get_method_attributes($class, $method, $attribute_class)[0] ?? null;
     }

     public function get_all_method_attributes(
         string|object $class,
         string $method
     ) : array {
         $class_name = is_object($class) ? $class::class : $class;

         $ref = new ReflectionMethod($class_name, $method);

         return array_map(fn($attr) => $attr->newInstance(), $ref->getAttributes());
     }

     public function get_methods_with_attribute(
         string $class,
         string $attribute_class,
         bool   $multiple = false
     ): array {

         $result = [];

         $reflection = new ReflectionClass($class);

         foreach($reflection->getMethods() as $method){
             $attributes = $method->getAttributes($attribute_class);

             if(!$attributes){
                 continue;
             }

             $result[$method->getName()] = !$multiple ? $attributes[0]->newInstance() : array_map(
                 fn($attribute) => $attribute->newInstance(),
                 $attributes
             );
         }

         return $result;
     }
}