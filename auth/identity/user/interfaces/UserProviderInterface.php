<?php
namespace SaQle\Auth\Identity\User\Interfaces;

interface UserProviderInterface {
     public function find(string|int $id): ?UserInterface;
}