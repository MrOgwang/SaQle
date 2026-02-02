<?php
namespace SaQle\Build\Utils;

use SaQle\Orm\Database\Schema;

class MigrationUtils {

     public static function is_schema_defined(string $schema_name){

         $schema = config('schemas', [])[$schema_name] ?? null;

         if(!$schema){
             return false;
         }

         if(!class_exists($schema) || !is_subclass_of($schema, Schema::class)){
             return false;
         }
         
         return true;
     }

	 public static function get_context_classes($db_context){
         //There must exist at least one db context class
         if(!config('db_context_classes')){
             throw new \Exception("The project has not declared any database context classes!");
         }

         /**
          * If a db context is provided, 
          * 1. Confirm that it exists in the db context classes list,
          * 2. Confirm that the class actually exists.
          * */
         $context_classes = [];
         $defined_classes = array_keys(config('db_context_classes'));
         if($db_context){
             for($x = 0; $x < count($defined_classes); $x++){
                 if(str_contains($defined_classes[$x], $db_context) && class_exists($defined_classes[$x])){
                     $context_classes[] = $defined_classes[$x];
                     break;
                 }
             }
         }else{
             for($x = 0; $x < count($defined_classes); $x++){
                 if( class_exists($defined_classes[$x]) ){
                     $context_classes[] = $defined_classes[$x];
                 }
             }
         }

         if(!$context_classes){
                throw new \Exception("Ensure there is at least one defined database context!");
         }

         return $context_classes;
     }

     public static function is_model_defined($model_class, $project_root){
         $mnparts = explode("\\", $model_class);
         $root = array_shift($mnparts);
         $root = strtolower($root);

         $model_file_path = strtolower(implode(DIRECTORY_SEPARATOR, $mnparts)).".php";
         if($root == "saqle"){
             $project_root_parts = explode(DIRECTORY_SEPARATOR, $project_root);
             array_pop($project_root_parts);
             $saqle_root = strtolower(implode(DIRECTORY_SEPARATOR, $project_root_parts))."/saqle";
             $model_file_path = $saqle_root."/".$model_file_path;
         }else{
             $model_file_path = $project_root."/".$model_file_path;
         }

         return file_exists($model_file_path);
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

     public static function get_path_from_namespace(string $namespace, $project_root){
         $mnparts = explode("\\", $namespace);
         $root = array_shift($mnparts);
         $root = strtolower($root);
         $path = strtolower(implode(DIRECTORY_SEPARATOR, $mnparts))."/";
         if($root == "saqle"){
             $project_root_parts = explode(DIRECTORY_SEPARATOR, $project_root);
             array_pop($project_root_parts);
             $saqle_root = strtolower(implode(DIRECTORY_SEPARATOR, $project_root_parts))."/saqle";
             $path = $saqle_root."/".$path;
         }else{
             $path = $project_root."/".$path;
         }
         return $path;
     }

}