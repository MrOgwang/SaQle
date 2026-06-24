<?php

namespace SaQle\Auth\Interfaces;

interface HashServiceInterface {
     public function make(string $value): string;

     public function verify(string $plain, string $hashed): bool;

     public function needs_rehash(string $hashed): bool;
}