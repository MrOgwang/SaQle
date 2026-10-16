<?php

namespace SaQle\Security\Csrf;

use RuntimeException;
use SessionHandlerInterface;
use SaQle\Core\Support\Session;

class CsrfToken {

     private const SESSION_KEY = 'csrf_token';

     private const TOKEN_BYTES = 32;

     //Get the current CSRF token, creating one if necessary.
     public static function get(): string {

         $token = Session::get(self::SESSION_KEY);

         if(!is_string($token) || $token === ''){

             $token = self::generate();

             Session::set(self::SESSION_KEY, $token);
         }

         return $token;
     }

     //Generate and replace the current CSRF token.
     public static function regenerate(): string {
         
         $token = self::generate();

         Session::set(self::SESSION_KEY, $token);

         return $token;
     }

     //Determine whether a submitted token is valid.
     public static function validate(?string $token = null): bool {

         if(!$token){
             return false;
         }

         $stored = Session::get(self::SESSION_KEY); 

         if(!is_string($stored) || $stored === ''){
             return false;
         }

         return hash_equals($stored, $token);
     }

     //Generate a cryptographically secure token.
     private static function generate(): string {
         return bin2hex(random_bytes(self::TOKEN_BYTES));
     }

     public static function get_key(){
         return self::SESSION_KEY;
     }
}