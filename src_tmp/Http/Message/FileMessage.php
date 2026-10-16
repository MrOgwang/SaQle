<?php

namespace SaQle\Http\Message;

use RuntimeException;
use SaQle\Http\HttpStatus;

final class FileMessage extends Message {  

     public function __construct(array $file, string $message = ''){
         
         parent::__construct(HttpStatus::OK, $message);

         $this->with('file', self::normalize_file($file));
     }

     private static function normalize_file(array $file){

         //at least path must be provided!
         if(!isset($file['path'])){
             throw new RuntimeException('File path not provided in FileMessage!');
         }

         $path = trim($file['path']);

         return [
             'path'   => $path,
             'name'   => $file['name'] ?? basename($path),
             'inline' => $file['inline'] ?? false,
             'cache'  => $file['cache'] ?? false,
             'mime'   => $file['mime'] ?? mime_content_type($path) ?: 'application/octet-stream',
             'size'   => $file['size'] ?? filesize($path)
         ];
     }

     public function get_file(): array {
         return $this->data['file'];
     }

     public static function from_path(
         string $path, 
         ?string $name = null, 
         bool $inline = true, 
         bool $cache = false, 
         ?string $mime = null, 
         ?int $size = null
     ) : static {
         return new static([
             'path'   => $path,
             'name'   => $name,
             'inline' => $inline,
             'cache'  => $cache,
             'mime'   => $mime,
             'size'   => $size
         ]);
     }

     public function download(?string $name = null) : self {

         $this->data['file']['inline'] = false;
         $this->data['file']['name'] = $name ?? $this->data['file']['name'];

         return $this;
     }

     public function inline(?string $name = null) : self {

         $this->data['file']['inline'] = true;
         $this->data['file']['name'] = $name ?? $this->data['file']['name'];
         $this->data['file']['cache']  = true;

         return $this;
     }
}