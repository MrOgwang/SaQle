<?php
namespace SaQle\Auth\Providers\Interfaces;

use SaQle\Auth\Models\Interfaces\IUser;

interface SessionProvider{
     /**
     * Called after successful login.
     * Should persist the user's identity (session ID, token, cookie, etc.).
     * Returns the session key or token string to send back to the client.
     */
     public function create_session(IUser $user): string;

     /**
     * Called on every request.
     * Extracts the user’s ID from the request (cookie, header, etc.).
     * Returns the user ID if found, or null if not authenticated.
     */
     public function get_user_id(): ?string;
}
