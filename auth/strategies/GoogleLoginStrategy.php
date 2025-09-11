<?php
namespace SaQle\Auth\Strategies;

use SaQle\Auth\Strategies\Interfaces\LoginStrategy;
use SaQle\Auth\Models\Interfaces\IUser;

class GoogleLoginStrategy implements LoginStrategy {
    public function authenticate(array $credentials): ?IUser {
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
