<?php

namespace SaQle\Core\Files;

class UploadedFile {
     public function __construct(
         public readonly string $name,
         public readonly string $tmp_name,
         public readonly int $size,
         public readonly int $error,
         public readonly string $type,
         public readonly bool $is_binary = true
     ) {}

     public function extension(): string {
         return pathinfo($this->name, PATHINFO_EXTENSION);
     }
}
