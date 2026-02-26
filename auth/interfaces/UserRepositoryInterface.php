<?php
namespace SaQle\Auth\Interfaces;

use SaQle\Auth\Interfaces\UserInterface;

interface UserRepositoryInterface {
     public function find_by_username(string $username) : ?UserInterface;
}