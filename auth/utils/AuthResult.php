<?php
namespace SaQle\Auth\utils;

use SaQle\Auth\Models\Interfaces\IUser;

class AuthResult {
     public function __construct(
        public bool    $success,
        public ?IUser  $user = null,
        public ?string $token = null,
        public ?string $message = null
     ) {}
}
