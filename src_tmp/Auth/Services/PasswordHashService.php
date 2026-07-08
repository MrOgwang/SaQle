<?php
namespace SaQle\Auth\Services;

use SaQle\Auth\Interfaces\HashServiceInterface;

class PasswordHashService implements HashServiceInterface {
     public function make(string $value): string {
         return password_hash($value, config('auth.passwords.default.algorithm', PASSWORD_DEFAULT));
     }

     public function verify(string $plain, string $hashed): bool {
         return password_verify($plain, $hashed);
     }

     public function needs_rehash(string $hashed): bool {
         return password_needs_rehash($hashed, config('auth.passwords.default.algorithm', PASSWORD_DEFAULT));
     }
} 