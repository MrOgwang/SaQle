<?php

namespace SaQle\Core\Files;

use SaQle\Core\Files\Storage\StorageFactory;
use RuntimeException;

class StoredFileFactory {

     private static function default_or_null(?string $default_url = null){
         
         if(!$default_url){
             return null;
         }

         return new StoredFile([], $default_url);
     }

     public static function make(
         ?string $json = null, 
         ?string $default_url = null,
         bool $is_multiple = true
     ) : null|StoredFile|StoredFileCollection {
         if(!$json){
             return self::default_or_null($default_url);
         }

         $meta = json_decode($json, true);

         if(!$meta){
             return self::default_or_null($default_url);
         }

         return $is_multiple ? 
         new StoredFileCollection($meta, $default_url) : 
         new StoredFile(isset($meta['name']) ? $meta : $meta[0], $default_url);
     }
}