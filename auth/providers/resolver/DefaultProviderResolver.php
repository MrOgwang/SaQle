<?php
namespace SaQle\Auth\Providers\Resolver;

use SaQle\Auth\Providers\Interfaces\ProviderResolver;
use SaQle\Auth\Providers\Interfaces\SessionProvider;
use SaQle\Auth\Providers\{PhpSessionProvider, JwtSessionProvider, ApiSessionProvider};

class DefaultProviderResolver implements ProviderResolver {
     public function resolve_provider(): SessionProvider {
         $headers = function_exists('getallheaders') ? getallheaders() : [];

         // --- Explicit mode ---
         if(isset($headers['X-Auth-Type'])){
             switch (strtolower($headers['X-Auth-Type'])) {
                 case 'jwt':
                     return new JwtSessionProvider();
                 case 'apikey':
                     return new ApiSessionProvider();
                 case 'session':
                 default:
                     return new PhpSessionProvider();
             }
         }

         // --- Heuristic mode ---
         if(isset($headers['Authorization'])) {
             if (stripos($headers['Authorization'], 'Bearer ') === 0) {
                return new JwtSessionProvider();
             }
         }

         if (!empty($_COOKIE[session_name()])) {
            return new PhpSessionProvider();
         }

         // Fallback
         return new PhpSessionProvider();
     }
}
