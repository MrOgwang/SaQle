<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use RuntimeException;

class OperationFactory {

     public static function make(string $action): MigrationOperation {
         
         $class_name = str_replace(' ', '', ucwords(str_replace('_', '', $action)));

         $class = "SaQle\\Core\\Migration\\Operations\\{$class_name}";

         if(!class_exists($class)){
             throw new RuntimeException( "Unknown migration operation: {$action}");
         }

         $operation = new $class();

         if(!$operation instanceof MigrationOperation){
             throw new RuntimeException("Migration operation {$class} must implement ".MigrationOperation::class);
         }

         return $operation;
     }
}