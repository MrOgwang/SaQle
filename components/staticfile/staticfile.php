<?php
namespace SaQle\Components\StaticFile;

use SaQle\Core\Files\Storage\StorageFactory;

class StaticFile {

     public function get(string $type, string $file){

         $allowed_types = ['css' => 'text/css', 'js' => 'application/javascript'];

         if(!isset($allowed_types[$type])){
             http_response_code(404);
             exit;
         }

         $path = path_join([config('base_path'), config('assets_cache_dir'), $file.".".$type]);
         log_to_file($path);

         if(!file_exists($path)){
             http_response_code(404);
             exit;
         }

         header("Content-Type: {$allowed_types[$type]}");
         readfile($path);

         exit;
     }
}