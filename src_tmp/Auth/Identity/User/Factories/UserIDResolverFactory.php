<?php
namespace SaQle\Auth\Identity\User\Factories;

use SaQle\Auth\Identity\User\Interfaces\UserIDResolverInterface;
use SaQle\Auth\Identity\User\Resolvers\{
     SessionUserIDResolver, 
     JwtUserIDResolver, 
     ApiUserIDResolver
};

class UserIDResolverFactory {

     public static function make() : UserIDResolverInterface {
         $headers = function_exists('getallheaders') ? getallheaders() : [];

         // --- Explicit mode ---
         if(isset($headers['X-Auth-Type'])){
             switch (strtolower($headers['X-Auth-Type'])){
                 case 'jwt':
                     return new JwtUserIDResolver();
                 case 'apikey':
                     return new ApiUserIDResolver();
                 case 'session':
                 default:
                     return new SessionUserIDResolver();
             }
         }

         // --- Heuristic mode ---
         if(isset($headers['Authorization'])) {
             if (stripos($headers['Authorization'], 'Bearer ') === 0) {
                 return new JwtUserIDResolver();
             }
         }

         if(!empty($_COOKIE[session_name()])) {
             return new SessionUserIDResolver();
         }

         // Fallback
         return new SessionUserIDResolver();
     }
}
