<?php
namespace SaQle\Build\Utils;

use SaQle\Orm\Database\Schema;
use SaQle\Orm\Entities\Model\Schema\Model;
use RuntimeException;

class MigrationUtils {

     public static function is_schema_defined(string $schema_name){

         $schema = config('db.schemas', [])[$schema_name] ?? null;

         if(!$schema){
             return false;
         }

         if(!class_exists($schema) || !is_subclass_of($schema, Schema::class)){
             return false;
         }
         
         return true;
     }

     public static function is_model_defined(string $model_class){
        
         if($model_class && class_exists($model_class) && is_subclass_of($model_class, Model::class)){
             return true;
         }

         return false;
     }

     public static function get_class_namespace(string $long_class_name){
         $nameparts = explode("\\", $long_class_name);
         array_pop($nameparts);
         return implode("\\", $nameparts);
     }

     public static function get_class_name(string $long_class_name, bool $include_namespace = false){
         if($include_namespace)
             return $long_class_name;

         $nameparts = explode("\\", $long_class_name);
         return end($nameparts);
     }

     public static function get_path_from_namespace(string $namespace){
         $namespace_parts = explode("\\", $namespace);
         $first = strtolower(array_shift($namespace_parts));

         if($first === 'saqle')
             throw new RuntimeException("Cannot write to framework root!");

         $base_path = config('base_path');

         return $base_path."/".implode("/", $namespace_parts);
     }

     public static function get_previous_snapshot($snapshot_name, $migration_name, $migration_timestamp, $migrations_folder){

         if(!$migration_name || !$migration_timestamp)
             return null;

         $migration_class = "Migration_{$migration_timestamp}_{$migration_name}";
         $migration_path = $migrations_folder."/".$migration_class.".php";

         if(!file_exists($migration_path))
             return null;

         require_once $migration_path;

         $migration_instance = new $migration_class();
         $snapshot_details = $migration_instance->snapshots()[$snapshot_name] ?? null;

         if(!$snapshot_details)
             return null;

         $snapshot_path = $snapshot_details['path'];
         if(!file_exists($snapshot_path))
             return null;

         require_once $snapshot_path;

         $snapshot_class = $snapshot_details['name'];

         return new $snapshot_class();
     }

}