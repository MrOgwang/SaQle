<?php
namespace SaQle\Auth\Identity\Resolvers;

use SaQle\Auth\Interfaces\{
     IdentityProviderInterface,
     IdentityProviderResolverInterface
};
use SaQle\Auth\Identity\Providers\{
     SessionIdentityProvider, 
     JwtIdentityProvider, 
     ApiIdentityProvider
};

class DefaultIdentityProviderResolver implements IdentityProviderResolverInterface {

     public function resolve(): IdentityProviderInterface {
         $headers = function_exists('getallheaders') ? getallheaders() : [];

         // --- Explicit mode ---
         if(isset($headers['X-Auth-Type'])){
             switch (strtolower($headers['X-Auth-Type'])){
                 case 'jwt':
                     return new JwtIdentityProvider();
                 case 'apikey':
                     return new ApiIdentityProvider();
                 case 'session':
                 default:
                     return new SessionIdentityProvider();
             }
         }

         // --- Heuristic mode ---
         if(isset($headers['Authorization'])) {
             if (stripos($headers['Authorization'], 'Bearer ') === 0) {
                 return new JwtIdentityProvider();
             }
         }

         if(!empty($_COOKIE[session_name()])) {
             return new SessionIdentityProvider();
         }

         // Fallback
         return new SessionIdentityProvider();
     }
}
