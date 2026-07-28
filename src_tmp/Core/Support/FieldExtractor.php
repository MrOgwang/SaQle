<?php

namespace SaQle\Core\Support;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use DateTimeInterface;

final class FieldExtractor {
     /**
     * @return Field[]
     */
     public static function extract(string|object $class): array {
         $reflection = new ReflectionClass($class);

         $defaults = $reflection->getDefaultProperties();

         $fields = [];

         foreach($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property){

             $name = $property->getName();

             $default = array_key_exists($name, $defaults) ? $defaults[$name] : null;

             $nullable = self::is_nullable($property);

             $type = self::resolve_field_type($property, $default);

             $field = new Field();

             $field->initialize( 
                 name: $name,
                 id: $name,
                 type: $type,
                 required: !$nullable,
                 default: $default,
                 nullable: $nullable,
                 readonly: $property->isReadOnly(),
                 promoted: $property->isPromoted()
             ); 

             $mapping_attr = $property->getAttributes(MapTo::class);
             $mapping_instance = $mapping_attr ? $mapping_attr[0]->newInstance() : null;

             if($mapping_instance){
                 
                 if(!$mapping_instance->field){
                     $mapping_instance->field($name);
                 }

                 if($mapping_instance->is_spread()){
                     $fields[] = $field;
                 }else{
                     $mapped_field = $mapping_instance->get_field();
                     $mapped_field->name($name)->default($default)->nullable($nullable)->required(!$nullable);

                     $fields[] = $mapped_field;
                 }
             }else{
                 $fields[] = $field;
             }
         }

         return $fields;
     }

     private static function is_nullable(ReflectionProperty $property): bool {
         $type = $property->getType();

         if(!$type){
             return true;
         }

         return $type->allowsNull();
     }

     private static function resolve_field_type(ReflectionProperty $property, mixed $default): string {

         $type = $property->getType();

         if($type instanceof ReflectionNamedType){
             return self::map_php_type($type->getName());
         }

         if($type instanceof ReflectionUnionType){
             foreach($type->getTypes() as $named_type){
                 if($named_type->getName() !== 'null') {
                     return self::map_php_type($named_type->getName());
                 }
             }
         }

         //infer from default value

         return match(true){
             is_int($default) => 'number',
             is_float($default) => 'number',
             is_bool($default) => 'checkbox',
             $default instanceof \DateTimeInterface => 'datetime',
             is_array($default) => 'array',
             default => 'text'
         };
     }

     private static function map_php_type(string $type): string {
         return match ($type) {
             'int', 'float'=> 'number',
             'string' => 'text',
             'bool' => 'checkbox',
             'array' => 'array',
             'DateTime', 'DateTimeImmutable', '\DateTime', '\DateTimeImmutable' => 'datetime',
             default => 'text'
         };
     }
}