<?php
namespace SaQle\Auth\Interfaces;

use SaQle\Auth\Interfaces\UserInterface;

interface IdentityProviderInterface {
     /**
     * Called after successful login.
     * Should persist the user's identity (session ID, token, cookie, etc.).
     * Returns the session key or token string to send back to the client.
     */
     public function create(UserInterface $user) : string;

     /**
     * Called on every request.
     * Extracts the user’s ID from the request (cookie, header, etc.).
     * Returns the user ID if found, or null if not authenticated.
     */
     public function user_id() : ? string;

     /**
      * Regenerate identity id
      * */
     public function regenerate() : void;

     /**
      * Destroy current identity
      * */
     public function destroy() : void;
}
