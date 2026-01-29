<?php

namespace SaQle\Core\Files;

class UploadedFile {
     public function __construct(
         public readonly string $original_name,
         public readonly string $tmp_path,
         public readonly int $size,
         public readonly string $mime_type,
         public readonly bool $is_binary = true
     ) {}

     public function get_stream(){
         return fopen($this->tmp_path, 'rb');
     }
}
