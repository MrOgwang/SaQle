<?php

namespace SaQle\Core\Files\Storage\Drivers;

interface IStorageDriver {
     public function put(string $path, mixed $contents): void;

     public function exists(string $path): bool;

     public function delete(string $path): void;

     public function read(string $path): mixed;

     public function url(string $path): ?string;

     public function path(string $path): ?string;
}
