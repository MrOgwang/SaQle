<?php
namespace SaQle\Auth\Providers;

use SaQle\Auth\Models\Interfaces\IUser;
use SaQle\Auth\Providers\Interfaces\SessionProvider;

class PhpSessionProvider implements SessionProvider {
     public function __construct(){
         if(session_status() === PHP_SESSION_NONE){
             session_start();
         }
     }

     public function create_session(IUser $user): string{
         $_SESSION['user_id'] = $user->user_id;
         return session_id();
     }

     public function get_user_id(): ?string{
         return $_SESSION['user_id'] ?? null;
     }
}
