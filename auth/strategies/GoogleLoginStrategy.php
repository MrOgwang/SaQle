<?php
namespace SaQle\Auth\Strategies;

use SaQle\Auth\Interfaces\LoginStrategyInterface;
use SaQle\Auth\Interfaces\UserInterface;

class GoogleLoginStrategy implements LoginStrategyInterface {
    public function authenticate(array $credentials): ?UserInterface {
        $idToken = $credentials['id_token'] ?? null;
        if (!$idToken) return null;

        // Verify with Google API / JWT library
        $googleUser = $this->verifyIdToken($idToken);

        if (!$googleUser) return null;

        // Create or fetch local user
        return User::firstOrCreate([
            'email' => $googleUser['email']
        ], [
            'name' => $googleUser['name']
        ]);
    }

    private function verifyIdToken(string $token): ?array {
        // TODO: use Google client libs to verify
        return [
            'email' => 'user@example.com',
            'name' => 'Alice Doe'
        ];
    }
}
