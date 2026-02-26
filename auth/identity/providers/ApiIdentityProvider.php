<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\Interfaces\{
     UserInterface,
     IdentityProviderInterface
};

class ApiIdentityProvider implements IdentityProviderInterface {
     public function create(UserInterface $user): string {
        // In API key systems, session is not created dynamically.
        // Instead, project would issue & store API keys separately.
        return "api-key-static";
     }

     public function user_id(): ?string {
         $api_key = $_SERVER['HTTP_X_API_KEY'] ?? null;
         if($api_key){
             //Lookup user by API key (project decides how)
             $user = User::findByApiKey($apiKey); 
             return $user?->getId();
         }
         return null;
     }

     public function regenerate() : void {
    
     }

     public function destroy() : void {
         //destroy api session
     }
}
