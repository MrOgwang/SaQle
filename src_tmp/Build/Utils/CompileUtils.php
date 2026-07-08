<?php

namespace SaQle\Build\Utils;

trait CompileUtils {
	 protected static function normalize_path(string $path){
        
         $owner = "project";

         if(str_starts_with($path, config('base_path'))){
             $path = str_replace(config('base_path').DIRECTORY_SEPARATOR, '', $path);
             $owner = "project";
         }elseif(str_starts_with($path, config('framework_path'))){
             $path = str_replace(config('framework_path').DIRECTORY_SEPARATOR, '', $path);
             $owner = "framework";
         }
         
         $path = str_replace('\\', '/', $path); // normalize slashes

         return [$path, $owner];
     }
}