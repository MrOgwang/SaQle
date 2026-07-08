<?php
namespace SaQle\Auth\Identity\User\Resolvers;

use SaQle\Auth\Identity\User\Interfaces\{
     UserInterface,
     UserIDResolverInterface
};

class SessionUserIDResolver implements UserIDResolverInterface {
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

     public function resolve(): ?string{
         return $_SESSION['user_id'] ?? null;
     }

     public function destroy() : void {
         //destry jwt session
         session_unset();
         session_destroy();
     }
}
