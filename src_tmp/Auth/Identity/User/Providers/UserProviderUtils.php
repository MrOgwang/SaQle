<?php

namespace SaQle\Auth\Identity\User\Providers;

use SaQle\Auth\Identity\User\Interfaces\UserInterface;
use SaQle\Auth\Context\ActorContext;

trait UserProviderUtils {

	 protected string $model_class;

     private function get_user_query(){
         if(ActorContext::is_platform()){
             return $this->model_class::using(system_connection())->get();
         }

         return $this->model_class::get();
     }

	 public function find(string|int $id): ?UserInterface {
         return $this->get_user_query()->where('user_id', $id)->first_or_null();
     }

     public function find_by_credentials(array $credentials) : ?UserInterface {
         $username = $credentials['username'] ?? null;
         $password = $credentials['password'] ?? null;

         if(!$username || !$password) return null;

         $user = $this->get_user_query()->where('username__eq', $username)->limit(1)->first_or_null();
         
         if(!$user) return null;

         if(!$this->hash_service->verify($password, $user->password)) return null;

         return $user;
     }
}