<?php

namespace SaQle\Auth\Identity\Tenant\Providers;

use SaQle\Auth\Identity\Tenant\Interfaces\{
     TenantProviderInterface,
     TenantInterface
};

class DefaultTenantProvider implements TenantProviderInterface {

     private string $model_class;

     public function __construct(){
         $this->model_class = config('tenancy.model_class');
     }

     public function find(string|int $id): ?TenantInterface {
         return $this->model_class::get()->where('tenant_id', $id)->first_or_null();
     }
}