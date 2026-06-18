<?php
namespace SaQle\Auth\Interfaces;

use SaQle\Auth\Identity\User\Interfaces\UserInterface;

interface UserRepositoryInterface {
     public function find_by_username(string $username) : ?UserInterface;
}