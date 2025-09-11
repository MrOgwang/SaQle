<?php
namespace SaQle\Auth\Providers;

use SaQle\Auth\Models\Interfaces\IUser;
use SaQle\Auth\Providers\Interfaces\SessionProvider;

class ApiSessionProvider implements SessionProvider{
     public function create_session(IUser $user): string {
        // In API key systems, session is not created dynamically.
        // Instead, project would issue & store API keys separately.
        return "api-key-static";
     }

     public function get_user_id(): ?string{
         $api_key = $_SERVER['HTTP_X_API_KEY'] ?? null;
         if($api_key){
             //Lookup user by API key (project decides how)
             $user = User::findByApiKey($apiKey); 
             return $user?->getId();
         }
         return null;
     }
}
