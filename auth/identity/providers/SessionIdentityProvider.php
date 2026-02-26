<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\Interfaces\UserInterface;
use SaQle\Auth\Interfaces\IdentityProviderInterface;

class SessionIdentityProvider implements IdentityProviderInterface {
     public function __construct(){
         if(session_status() === PHP_SESSION_NONE){
             session_start();
         }
     }

     public function create(UserInterface $user): string{
         session_regenerate_id();
         $_SESSION['user_id'] = $user->user_id;
         return session_id();
     }

     public function regenerate() : void {
    
     }

     public function user_id(): ?string{
         return $_SESSION['user_id'] ?? null;
     }

     public function destroy() : void {
         //destry jwt session
         session_unset();
         session_destroy();
     }
}
