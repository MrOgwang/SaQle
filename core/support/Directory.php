<?php

namespace SaQle\Core\Support;

use InvalidArgumentException;

class Directory {

     const DIR_PERMISSION = 0755;

     protected array $blueprints = [];

     public function __construct(){
         $this->blueprints = config('dir.blueprints', []);
     }

     public function create(string $path): string {
         if(!is_dir($path)){
             $old = umask(0);
             mkdir($path, self::DIR_PERMISSION, true);
             umask($old);
         }

         return $path;
     }

     public function path(string $key, array $context = []) : string {
         if(!isset($this->blueprints[$key])){
             throw new InvalidArgumentException("Directory blueprint [$key] is not defined.");
         }

         return $this->resolve_blueprint($this->blueprints[$key], $context);
     }

     public function file_path(string $key, string $original_name, array $context = [], bool $random = false): string {

         $extension = pathinfo($original_name, PATHINFO_EXTENSION);

         $dir = $this->path($key, $context);

         $filename = $random ? $this->random_file_name($extension) : $original_name;

         return path_join([$dir, $filename]);
     }

     protected function resolve_blueprint(string $template, array $context): string {
         return preg_replace_callback('/{{\s*(.*?)\s*}}/', function($matches) use ($context){
                 $key = $matches[1];

                 if(!array_key_exists($key, $context)){
                     throw new InvalidArgumentException("Missing context value [$key] for directory resolution.");
                 }

                return $context[$key];
             },

             $template
         );
     }

     protected function random_file_name(string $extension): string {
         return bin2hex(random_bytes(8)).'.'.ltrim($extension, '.');
     }
}
