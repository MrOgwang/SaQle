<?php
namespace SaQle\Auth\Interfaces;

interface UserRegistrationInterface {
     public function register(...$data): mixed;
}