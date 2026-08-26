<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\Identity\Tenant\Interfaces\TenantIDResolverInterface;

class PathTenantIDResolver implements TenantIDResolverInterface {

	 public function __construct(private string $key){}

	 public function resolve() : null|int|string {

         $slug = request()->params->get($this->key, null);

         if(is_null($slug)){
         	return null;
         }

         $model_class = config('tenancy.model_class');

         $tenant = $model_class::using(system_connection())
         ->get()->select(['tenant_id'])->where('slug__eq', $slug)->first_or_null();

         return $tenant ? $tenant->tenant_id : null;
         
	 }
}