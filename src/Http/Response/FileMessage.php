<?php

namespace SaQle\Http\Response;

use RuntimeException;

final class FileMessage extends Message {

     public function __construct(array $file_info, string $message = ''){
         parent::__construct(
             self::OK, 
             self::normalize_file_info($file_info), 
             $message
         );
     }

     private static function normalize_file_info(array $file_info){

         //at least path must be provided!
         if(!isset($file_info['path'])){
             throw new RuntimeException('File path not provided in FileMessage!');
         }

         $path = trim($file_info['path']);

         return [
             'path'   => $path,
             'name'   => $file_info['name'] ?? basename($path),
             'inline' => $file_info['inline'] ?? false,
             'cache'  => $file_info['cache'] ?? false,
             'mime'   => $file_info['mime'] ?? mime_content_type($path) ?: 'application/octet-stream',
             'size'   => $file_info['size'] ?? filesize($path)
         ];
     }

     public function get_file_info(): array {
         return $this->data;
     }

     public static function from_path(string $path, ?string $name = null, bool $inline = true, bool $cache = false, ?string $mime = null, ?int $size = null
     ) : self {
         return new self(self::normalize_file_info([
             'path'   => $path,
             'name'   => $name,
             'inline' => $inline,
             'cache'  => $cache,
             'mime'   => $mime,
             'size'   => $size
         ]));
     }

     public function download(?string $name = null) : self {
         $this->data['inline'] = false;
         $this->data['name']   = $name ?? $this->data['name'];
     }

     public function inline(?string $name = null) : self {
         $this->data['inline'] = true;
         $this->data['cache']  = true;
         $this->data['name']   = $name ?? $this->data['name'];
     }
}