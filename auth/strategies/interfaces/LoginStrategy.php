<?php
namespace SaQle\Auth\Strategies\Interfaces;

use SaQle\Auth\Models\Interfaces\IUser;

interface LoginStrategy {
     /**
     * Attempt to authenticate with given credentials.
     * Returns User on success, null on failure.
     */
     public function authenticate(array $credentials): ?IUser;
}
