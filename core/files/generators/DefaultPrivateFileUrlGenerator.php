<?php

namespace SaQle\Core\Files\Generators;

use SaQle\Core\Files\Storage\Drivers\IStorageDriver;

class DefaultPrivateFileUrlGenerator implements PrivateFileUrlGeneratorInterface {

     public function __construct(
         protected IStorageDriver $driver
     ){}

     public function generate(array $file_meta): string {

         $token = encrypt(json_encode($file_meta), config('app.media_encrypt_key'), config('app.media_encrypt_salt'));

         $token = base64_to_url($token);

         return '/private-file?f='.rawurlencode($token);
     }
}