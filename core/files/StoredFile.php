<?php

namespace SaQle\Core\Files;

use SaQle\Core\Files\Storage\StorageFactory;
use RuntimeException;
use JsonSerializable;

class StoredFile implements JsonSerializable{

     protected array $meta = [];
     protected ?string $default_url = null;

     /**
     * Constructor
     *
     * @param array $meta Metadata from DB, e.g.
     * [
     *   'storage' => 'public',
     *   'path' => 'users/profile',
     *   'name' => 'abc123.jpg',
     *   'original_name' => 'me.jpg',
     *   'size' => 102392,
     *   'mime' => 'image/jpg'
     * ]
     */
     public function __construct(array $meta = [], ?string $default_url = null){
         $this->meta = $meta;
         $this->default_url = $default_url;
     }

     public function jsonSerialize() : mixed {
         return $this->url();
     }

     /**
     * Create StoredFile from JSON stored in DB
     */
     public static function from_json(?string $json): ? self {
         if(!$json){
             return null;
         }

         $meta = json_decode($json, true);

         if(!$meta || !isset($meta['name'])){
             return null;
         }

         return new self($meta);
     }

     /**
     * Create StoredFile representing a default URL
     */
     public static function default(string $url): self {
         return new self([], $url);
     }

     private function get_storage(){
         return StorageFactory::make($this->meta['storage']);
     }

     /**
     * Get the URL to the file
     */
     public function url(): string {
         return $this->default_url ?: $this->get_storage()->url($this->meta);
     }

     /**
     * Get full storage path
     */
     public function path(): string {
         if($this->default_url){
            throw new RuntimeException('Path not available!');
         }

         return $this->get_storage()->path(path_join([$this->meta['path'], $this->meta['name']]));
     }

     /**
     * File size in bytes
     */
     public function size(): int {
         return $this->default_url ? 0 : ($this->meta['size'] ?? 0);
     }

     /**
     * Original file name
     */
     public function original_name(): string {
          return $this->default_url ? "" : ($this->meta['original_name'] ?? $this->meta['name']);
     }

     /**
     * Disk name
     */
     public function storage(): string {
         if($this->default_url){
            throw new RuntimeException('Storage not available!');
         }

         return $this->meta['storage'];
     }

     /**
     * File name (internal storage)
     */
     public function name(): string {
         return $this->default_url ? "" : $this->meta['name'];
     }

     /**
     * Magic __toString returns URL
     */
     public function __toString(): string {
         return $this->url();
     }

     /**
     * Optional: delete file
     * 
     * This must be relooked at later.
     */
     public function delete(): bool {
         if($this->default_url){
             return false;
         }

         return $this->get_storage()->delete(path_join([$this->meta['path'], $this->meta['name']]));
     }

     /**
     * TO DO
     */
     public function temporary_url(\DateTimeInterface $expires): string {
         /*return Storage::disk($this->disk())->temporaryUrl(
            $this->meta['path'] . '/' . $this->name(),
            $expires
         );*/
         return $this->url();
     }
}