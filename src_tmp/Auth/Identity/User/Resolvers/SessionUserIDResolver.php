<?php
namespace SaQle\Auth\Identity\User\Resolvers;

use SaQle\Auth\Identity\User\Interfaces\{
     UserInterface,
     UserIDResolverInterface
};
use SaQle\Core\Support\Session;
use RuntimeException;

class SessionUserIDResolver implements UserIDResolverInterface {
     
     public function __construct(){
         if(session_status() === PHP_SESSION_NONE){
             session_start();
         }
     }

     public function create(UserInterface $user): string {
         
         if(!session_regenerate_id()){
             throw new RuntimeException('Failed to regenerate session ID.');
         }

         Session::set('user_id', $user->user_id);

         //if multitenancy is set, store the tenant id as well.
         if(config('tenancy.enabled')){
             Session::set('tenant_id', $user->tenant_id);
         }

         return session_id();
     }

     public function regenerate() : void {
    
     }

     public function resolve(): ?string {
         return Session::get('user_id');
     }

     public function destroy() : void {

         session_unset();

         session_destroy();

     }
}
