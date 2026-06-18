<?php
namespace SaQle\Auth\Identity\User\Resolvers;

use SaQle\Auth\Identity\User\Interfaces\{
     UserInterface,
     UserIDResolverInterface
};

class ApiUserIDResolver implements UserIDResolverInterface {
     public function create(UserInterface $user): string {
        // In API key systems, session is not created dynamically.
        // Instead, project would issue & store API keys separately.
        return "api-key-static";
     }

     public function resolve(): ?string {
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
