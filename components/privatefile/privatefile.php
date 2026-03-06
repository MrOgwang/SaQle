<?php
namespace SaQle\Components\PrivateFile;

use SaQle\Core\Files\Storage\StorageFactory;

class PrivateFile {

     public function get(?string $f = null){
         if(!$f){
             http_response_code(404);
             exit;
         }

         $url = url_to_base64($f);

         $decrypted = decrypt($url, config('app.media_encrypt_key'), config('app.media_encrypt_salt'));

         if(!$decrypted){
             http_response_code(404);
             exit;
         }

         $meta = json_decode($decrypted, true);

         if(!$meta){
             http_response_code(404);
             exit;
         }

         $keys = ['storage', 'path', 'name', 'original_name', 'size', 'mime'];

         foreach($keys as $k){
             if(!isset($meta[$k])){
                 http_response_code(404);
                 exit;
             }
         }

         $storage = StorageFactory::make($meta['storage']);

         $path = $storage->path($meta['path']);

         if(!file_exists($path)){
             log_to_file("File does not exist: $path");
             http_response_code(404);
             exit();
         }

         if(!is_readable($path)){
             log_to_file("File is not readable: $path");
             http_response_code(404);
             exit;
         }

         $file_name = $meta['original_name'];

         $mime = mime_content_type($path) ?: 'application/octet-stream';
         $is_inline = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/') || $mime === 'application/pdf';

         header('Content-Type: '.$mime);
         header('Content-Disposition: '.($is_inline ? 'inline' : 'attachment').';filename="'.$file_name.'"');
         header('Content-Length: '.filesize($path));

         header('Cache-Control: public, max-age=31536000, immutable');
         header('Expires: '.gmdate('D, d M Y H:i:s', time() + 31536000).' GMT');

         if(ob_get_level()){
             ob_end_clean();
         }

         readfile($path);

         exit;
     }
}