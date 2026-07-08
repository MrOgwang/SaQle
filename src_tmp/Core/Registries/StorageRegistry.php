<?php
namespace SaQle\Core\Registries;

use SaQle\Core\Files\Storage\Storage;

class StorageRegistry {
     
     protected array $storages = [];

     public function get(string $name): Storage {
         return $this->storages[$name];
     }

     public function add(string $name, Storage $storage): void {
         $this->storages[$name] = $storage;
     }
}
