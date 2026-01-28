<?php

namespace SaQle\Security\Validation\Types;

use LogicException;

class ValueTypeResolver {

     public static function resolve(mixed $value): ValueType {

         if(is_int($value) || is_float($value)){
             return ValueType::NUMBER;
         }

         if(is_string($value) || is_null($value)){
             return ValueType::TEXT;
         }

         if(is_array($value) && isset($value['size'])){
             return ValueType::FILE;
         }

         if(is_array($value)){
             return ValueType::ARRAY;
         }

         throw new LogicException('Unsupported value type: ' . gettype($value));
     }
}
