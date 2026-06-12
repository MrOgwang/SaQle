<?php

namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\Interfaces\TenantProviderInterface;
use SaQle\Auth\Interfaces\TenantInterface;

class DefaultTenantProvider implements TenantProviderInterface {
     public function __construct(
         protected string $model_class
     ){}

     public function find(string|int $id): ?TenantInterface {
         return $this->model_class::get()->where('tenant_id', $id)->first_or_null();
     }
}