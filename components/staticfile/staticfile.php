<?php
namespace SaQle\Components\StaticFile;

use SaQle\Core\Files\Storage\StorageFactory;

class StaticFile {

     public function get(string $type, string $file) {
         $allowed_types = [
             'css' => 'text/css',
             'js'  => 'application/javascript'
         ];

         if(!isset($allowed_types[$type])) {
             http_response_code(404);
             exit;
         }

         //strict filename validation (VERY IMPORTANT)
         //allows only letters, numbers, dash, underscore
         if(!preg_match('/^[a-zA-Z0-9_-]+$/', $file)){
             http_response_code(400);
             exit;
         }

         //build base directory safely
         $base_dir = realpath(path_join([config('base_path'), config('assets_cache_dir')]));

         if($base_dir === false){
             http_response_code(500);
             exit;
         }

         $path = realpath(path_join([$base_dir, $file.'.'.$type]));

         //ensure file exists AND is inside base directory
         if($path === false || !str_starts_with($path, $base_dir) || !is_file($path)){
             http_response_code(404);
             exit;
         }

         //(security + caching)
         header("Content-Type: {$allowed_types[$type]}");
         header("X-Content-Type-Options: nosniff"); // prevent MIME sniffing
         header("Cache-Control: public, max-age=31536000"); // 1 year
         header("Content-Length: ".filesize($path));

         readfile($path);
         
         exit;
     }
}