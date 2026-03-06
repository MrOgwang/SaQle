<?php

namespace SaQle\Core\Files\Storage\Drivers;

class LocalStorageDriver implements IStorageDriver {

     public function __construct(
         protected array $config
     ){}

     public function full_path(string $path): string {
         return path_join([$this->config['root'], $path]);
     }

     public function config(): array {
         return $this->config;
     }

     public function put(string $path, mixed $contents): void {
         $full = $this->full_path($path);
         $dir  = dirname($full);

         if(!is_dir($dir)){
             mkdir($dir, 0777, true);
         }

         if(is_resource($contents)){
             $dest = fopen($full, 'wb');
             stream_copy_to_stream($contents, $dest);
             fclose($dest);
         }else{
             file_put_contents($full, $contents);
         }
     }

     public function exists(string $path): bool {
         return file_exists($this->full_path($path));
     }

     public function delete(string $path): void {
         @unlink($this->full_path($path));
     }

     public function read(string $path): mixed {
         return fopen($this->full_path($path), 'rb');
     }

     public function public_url(string $path): ?string {

         if(($this->config['visibility'] ?? null) !== 'public'){
             return null;
         }

         return path_join([$this->config['base_url'], $path]);
     }

     public function path(string $path): ?string {
         return $this->full_path($path);
     }
}
