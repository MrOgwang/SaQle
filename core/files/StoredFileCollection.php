<?php

namespace SaQle\Core\Files;

use SaQle\Core\Files\Storage\StorageFactory;
use JsonSerializable;

class StoredFileCollection extends TypedCollection implements JsonSerializable {

     protected string $default_url = null;

     public function __construct(array $elements = [], ?string $default_url = null){
         $this->default_url = $default_url;
         parent::__construct($elements);
     }

     protected function type(): string {
         return StoredFile::class;
     }

     public function jsonSerialize() : mixed {
         return $this->urls();
     }

     public function first(): mixed {
         return $this->elements[0] ?? new StoredFile([], $this->default_url);
     }

     public function last(): mixed {
         if(empty($this->elements)){
             return new StoredFile([], $this->default_url);
         }

         return $this->elements[array_key_last($this->elements)];
     }

     public function items(): array {
        return $this->elements ?: [new StoredFile([], $this->default_url)];
     }

     /**
     * Create collection from JSON stored in DB
     */
     public static function from_json(?string $json = null, ?string $default_url = null): self {
         if(!$json){
             return new self([], $default_url);
         }

         $meta_array = json_decode($json, true) ?? [];

         $files = array_map(function ($meta){
             return new StoredFile($meta);
         }, $meta_array);

         return new self($files, $default_url);
     }

     /**
     * Convert collection to array of URLs
     */
     public function urls(): array {
         if(empty($this->elements) && $this->default_url) return [$this->default_url];

         return array_map(fn ($file) => $file->url(), $this->elements);
     }

     /**
     * Convert collection to array of paths
     */
     public function paths(): array {
        return array_map(fn ($file) => $file->path(), $this->files);
     }

     /**
     * Convert collection to array of original names
     */
     public function original_names(): array {
        return array_map(fn ($file) => $file->original_name(), $this->files);
     }

     /**
     * Convert collection to array of sizes
     */
     public function sizes(): array {
        return array_map(fn ($file) => $file->size(), $this->files);
     }

     /**
     * Convert collection to array of names
     */
     public function names(): array {
        return array_map(fn ($file) => $file->name(), $this->files);
     }

     /**
     * Convert collection to array of storages
     */
     public function storages(): array {
        return array_map(fn ($file) => $file->storage(), $this->files);
     }

     /**
     * Delete all files
     */
     public function delete(): void {
         foreach ($this->files as $file) {
            $file->delete();
         }
     }

     
}
