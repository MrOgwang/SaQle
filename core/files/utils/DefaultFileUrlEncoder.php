<?php

namespace SaQle\Core\Files\Utils;

use SaQle\Core\Files\Storage\Drivers\IStorageDriver;
use SaQle\Core\Support\FileUrlEncoderInterface;

class DefaultFileUrlEncoder implements FileUrlEncoderInterface {

     public function __construct(
         protected IStorageDriver $driver
     ){}

     public function encode(array $file_meta) : string {
         return base64_to_url(
             encrypt(
                 json_encode($file_meta), 
                 config('app.media_encrypt_key'), 
                 config('app.media_encrypt_salt')
             )
         );
     }

     public function decode(string $encoded) : array {

         $decrypted = decrypt($encoded, config('app.media_encrypt_key'), config('app.media_encrypt_salt'));
         if(!$decrypted){
             throw internal_server_error_exception();
         }

         $meta = json_decode($decrypted, true);
         if(!$meta){
             throw internal_server_error_exception();
         }

         return $meta;
     }
}