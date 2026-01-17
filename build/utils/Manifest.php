<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The Manifest stores a record of what each file looked like at the end of the last build, so the next build knows which files have changed.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Build\Utils;

class Manifest{
     protected string $file;

     public array $data = [];

     public function __construct(){

         $this->file = path_join([config('base_path'), '/storage/framework/build/manifest.json']);

         //ensure directory exists
         if (!is_dir(dirname($this->file))){
             mkdir(dirname($this->file), 0777, true);
         }

         if(file_exists($this->file)){
             $this->data = json_decode(file_get_contents($this->file), true) ?? [];
         }
     }

     public function get(string $path): ?array{
         return $this->data[$path] ?? null;
     }

     public function set(string $path, array $info): void{
         $this->data[$path] = $info;
     }

     public function remove(string $path): void{
         unset($this->data[$path]);
     }

     public function save(): void{
         //Create directory if missing
         if (!is_dir(dirname($this->file))) {
             mkdir(dirname($this->file), 0777, true);
         }

         file_put_contents($this->file, json_encode($this->data, JSON_PRETTY_PRINT));
     }
}
