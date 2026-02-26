<?php

namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\Interfaces\UserProviderInterface;
use SaQle\Auth\Interfaces\UserInterface;

class DefaultUserProvider implements UserProviderInterface {
     public function __construct(
         protected string $model_class
     ) {}

     public function find(string|int $id): ?UserInterface {
         return $this->model_class::get()->where('user_id', $id)->first_or_null();
     }
}