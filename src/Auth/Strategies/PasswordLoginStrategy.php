<?php
namespace SaQle\Auth\Strategies;

use SaQle\Auth\Interfaces\{
    HashServiceInterface,
    LoginStrategyInterface
};
use SaQle\Auth\Identity\User\Interfaces\{
     UserInterface,
     UserProviderInterface
};

class PasswordLoginStrategy implements LoginStrategyInterface {
     
     public function __construct(
         private UserProviderInterface $user_provider,
         private HashServiceInterface  $hash_service
     ){}

     public function authenticate(array $credentials): ?UserInterface {
         $user = $this->user_provider->find_by_credentials($credentials);

         if($user){
             if($this->hash_service->needs_rehash($user->password)){
                 $user->password = $this->hash_service->make($user->password);
                 //$user->save();
             }
         }

         return $user;
     } 
}
