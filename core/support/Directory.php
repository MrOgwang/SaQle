<?php

namespace SaQle\Core\Support;

use InvalidArgumentException;

class Directory {

     const DIR_PERMISSION = 0755;

     protected array $blueprints = [];

     public function __construct(){
         $this->blueprints = config('dir.blueprints', []);
     }

     public function path(string $key, array $context = []): string {
         if(!isset($this->blueprints[$key])){
             throw new InvalidArgumentException("Directory blueprint [$key] is not defined.");
         }

         $folder = $this->resolve_blueprint($this->blueprints[$key], $context);

         return $this->create_dir($folder);
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

     protected function create_dir(string $dir): string {
         $base = $this->get_media_root();

         $path = path_join([$base, $dir], true);

         if(!is_dir($path)){
             $old = umask(0);
             mkdir($path, self::DIR_PERMISSION, true);
             umask($old);
         }

         return $path;
     }

     protected function get_media_root(): string {
         $media_folder = config('app.media_folder');

         return config('app.hidden_media_folder') ? 
         path_join([config('base_path'), $media_folder], true) : path_join([config('document_root'), $media_folder], true);
     }

     public function file_path(string $key, string $original_name, array $context = [], bool $random = false): string {

         $extension = pathinfo($original_name, PATHINFO_EXTENSION);

         $dir = $this->path($key, $context);

         $filename = $random ? $this->random_file_name($extension) : $original_name;

         return path_join([$dir, $filename]);
     }

     protected function random_file_name(string $extension): string {
         return bin2hex(random_bytes(8)).'.'.ltrim($extension, '.');
     }
}
