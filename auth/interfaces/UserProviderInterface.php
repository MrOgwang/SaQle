<?php

namespace SaQle\Auth\interfaces;

use SaQle\Auth\Interfaces\UserInterface;

interface UserProviderInterface {
     public function find(string|int $id): ?UserInterface;
}