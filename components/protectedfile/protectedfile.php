<?php
namespace SaQle\Components\ProtectedFile;

use SaQle\Core\Files\Storage\StorageFactory;
use SaQle\Core\Support\BindFrom;

class ProtectedFile {

     protected function authorize(array $meta) : bool {
         return true;
     }

     protected function unauthorized(array $meta) : void {
         throw authorization_exception("You do not have permission to view this file!");
     }

     protected function not_found(string $path, array $meta) : void {
         throw not_found_exception("File does not exist: $path");
     }

     public function serve(
         string $storage_key,
         string $file
     ) {

         $file = trim($file);
         if(!$file){
             throw bad_request_exception('File is missing!');
         }

         $file = url_to_base64($file);
         $storage_config = config('app.media_storage_drivers')[$storage_key];

         $url_encoder_class = $storage_config['url_encoder'] ?? null;
         if(!$url_encoder_class || !class_exists($url_encoder_class)){
             throw bad_request_exception("Url generator class not found for storage: {$storage_key}!");
         }

         $storage_driver_class = $storage_config['driver'] ?? null;
         if(!$storage_driver_class || !class_exists($storage_driver_class)){
             throw bad_request_exception("Driver class not defined for storage: {$storage_key}!");
         }

         $storage_driver = new $storage_driver_class($storage_config);
         $url_encoder = new $url_encoder_class($storage_driver);

         $file_meta = $url_encoder->decode($file);

         if(!$this->authorize($file_meta)){
             $this->unauthorized($file_meta);
         }

         $storage = StorageFactory::make($file_meta['storage']);

         $path = $storage->path($file_meta['path']);
         $name = $file_meta['original_name'];

         if(!file_exists($path)){
             $this->not_found($path, $file_meta);
         }

         if(!is_readable($path)){
             throw internal_server_error_exception("File is not readable: $path");
         }

         $mime = mime_content_type($path) ?: 'application/octet-stream';
         $is_inline = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/') || $mime === 'application/pdf';

         return ok([
             'size' => filesize($path),
             'mime' => $mime,
             'inline' => $is_inline,
             'name' => $name,
             'path' => $path,
             'cache' => true
         ]);
     }
}