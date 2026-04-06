<?php
namespace SaQle\Components\StaticFile;

use SaQle\Core\Files\Storage\StorageFactory;

class StaticFile {

     public function get(string $type, string $file) {
         $allowed_types = [
             'css' => 'text/css',
             'js'  => 'application/javascript'
         ];

         if(!isset($allowed_types[$type])){
             throw not_found_exception();
         }

         //strict filename validation (VERY IMPORTANT)
         //allows only letters, numbers, dash, underscore
         if(!preg_match('/^[a-zA-Z0-9_-]+$/', $file)){
             throw bad_request_exception();
         }

         //build base directory safely
         $base_dir = realpath(path_join([config('base_path'), config('assets_cache_dir')]));

         if($base_dir === false){
             throw internal_server_error_exception();
         }

         $path = realpath(path_join([$base_dir, $file.'.'.$type]));

         //ensure file exists AND is inside base directory
         if($path === false || !str_starts_with($path, $base_dir) || !is_file($path)){
             throw not_found_exception();
         }

         return ok([
             'size' => filesize($path),
             'mime' => $allowed_types[$type],
             'inline' => false,
             'name' => "asset",
             'path' => $path,
             'cache' => true
         ]);
     }
}