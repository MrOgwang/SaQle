<?php
namespace SaQle\Auth\Identity\User\Providers;

use SaQle\Auth\Identity\User\Interfaces\UserProviderInterface;
use SaQle\Auth\Models\PlatformUser;
use SaQle\Auth\Interfaces\HashServiceInterface;

class PlatformUserProvider implements UserProviderInterface {

     use UserProviderUtils;
     
     public function __construct(
         private HashServiceInterface $hash_service
     ){
         $this->model_class = PlatformUser::class;
     }
}