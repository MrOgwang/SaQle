<?php
namespace SaQle\Modules\Asset\Components\ProtectedFile;

use SaQle\Core\Files\Storage\StorageFactory;
use SaQle\Http\Response\Message;
use SaQle\Core\Files\Utils\DefaultFileUrlEncoder;

class Controller {

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
     ){

         $file = trim($file);

         if(!$file){
             throw bad_request_exception('File is missing!');
         }

         $file = url_to_base64($file);
         $storage_config = config('app.media_storage_drivers')[$storage_key];

         $url_encoder = new DefaultFileUrlEncoder();

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

         $file = [
             'mime' => $mime,
             'inline' => $is_inline,
             'name' => $name,
             'path' => $path,
             'cache' => true
         ];
         
         return Message::file($file);
     }
}