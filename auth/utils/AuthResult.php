<?php
namespace SaQle\Auth\utils;

use SaQle\Auth\Interfaces\UserInterface;

class AuthResult {
     public function __construct(
        public bool    $success,
        public ?UserInterface  $user = null,
        public ?string $token = null,
        public ?string $message = null,
        public ?string $next = null //url to redirect user when authentication succeeds for web requests
     ) {}
}
