<?php

namespace SaQle\Http\Request\Execution;

/**
 * Check whether a parameter is a simple type
 * */
use ReflectionType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use ReflectionNamedType;

final class TypeInspector {

     private const SIMPLE_TYPES = [
         'int',
         'float',
         'string',
         'bool',
         'array',
         'callable',
         'iterable',
         'mixed',
     ];

     public static function is_simple_type(?ReflectionType $type): bool {

         if(!$type){
             return true; // untyped params behave like mixed
         }

         // Union types: int|string|null
         if($type instanceof ReflectionUnionType){
             foreach($type->getTypes() as $inner){
                 if(!self::is_simple_type($inner)){
                     return false;
                 }
             }
             return true;
         }

         //Intersection types (PHP 8.1+): A&B → NEVER simple
         if($type instanceof ReflectionIntersectionType){
             return false;
         }

         //Named type
         if($type instanceof ReflectionNamedType){

             if($type->isBuiltin()){
                 return in_array($type->getName(), self::SIMPLE_TYPES, true);
             }

             return false; // class / interface / enum
         }

         return false;
     }

     public static function is_class_type(?ReflectionType $type): bool{
         if (!$type) return false;

         if ($type instanceof ReflectionUnionType){
             foreach ($type->getTypes() as $inner) {
                 if (self::is_class_type($inner)) return true;
             }
             return false;
         }

         if ($type instanceof ReflectionNamedType){
             return !$type->isBuiltin();
         }

         return false;
     }

     public static function get_class_name(?ReflectionType $type): ?string{
         if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
             return $type->getName();
         }

         return null;
     }
}
