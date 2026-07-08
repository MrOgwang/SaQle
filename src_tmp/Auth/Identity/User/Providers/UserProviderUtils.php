<?php

namespace SaQle\Auth\Identity\User\Providers;

use SaQle\Auth\Identity\User\Interfaces\UserInterface;

trait UserProviderUtils {

	 protected string $model_class;

	 public function find(string|int $id): ?UserInterface {
         return $this->model_class::get()->where('user_id', $id)->first_or_null();
     }

     public function find_by_credentials(array $credentials) : ?UserInterface {
         $username = $credentials['username'] ?? null;
         $password = $credentials['password'] ?? null;

         if(!$username || !$password) return null;

         $user = $this->model_class::get()->where('username__eq', $username)->limit(1)->first_or_null();
         
         if(!$user) return null;

         if(!$this->hash_service->verify($password, $user->password)) return null;

         return $user;
     }
}