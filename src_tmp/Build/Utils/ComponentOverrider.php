<?php

namespace SaQle\Build\Utils;

use RuntimeException;

final class ComponentOverrider {

     public static function override(string $name, string $source) : string {

         $destination = self::get_destination($name);

         if(is_dir($destination)){
             throw new RuntimeException("Component already exists at '{$destination}'.");
         }

         self::copy_directory($source, $destination);

         return $destination;
     }

     private static function get_destination(string $name): string {

         $name = str_replace(" ", "_", ucwords(str_replace(".", " ", $name)));

         return path_join([config('base_path'), "src", "Components", $name]);
     }

     private static function copy_directory(string $source, string $destination): void {

         if(!mkdir($destination, 0777, true) && !is_dir($destination)){
             throw new RuntimeException("Unable to create directory '{$destination}'.");
         }

         $files = scandir($source);

         if($files === false){
             throw new RuntimeException("Unable to read directory '{$source}'.");
         }

         foreach($files as $file){
             if($file === '.' || $file === '..'){
                 continue;
             }

             $source_path = path_join([$source, $file]);
             $destination_path = path_join([$destination, $file]);

             if(is_dir($source_path)){
                 self::copy_directory($source_path, $destination_path);

                 continue;
             }

             if(!copy($source_path, $destination_path)){
                 throw new RuntimeException("Unable to copy '{$source_path}'.");
             }
         }
     }
}