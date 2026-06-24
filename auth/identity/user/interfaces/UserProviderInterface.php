<?php
namespace SaQle\Auth\Identity\User\Interfaces;

interface UserProviderInterface {
     public function find(string|int $id) : ?UserInterface;
     public function find_by_credentials(array $credentials) : ?UserInterface;
}