<?php
namespace SaQle\Auth\Interfaces;

use SaQle\Auth\Identity\User\Interfaces\UserInterface;

interface LoginStrategyInterface {
     /**
     * Attempt to authenticate with given credentials.
     * Returns User on success, null on failure.
     */
     public function authenticate(array $credentials): ?UserInterface;
}
