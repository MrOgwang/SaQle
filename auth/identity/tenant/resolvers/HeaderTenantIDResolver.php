<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class HeaderTenantIDResolver implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {
	 	 return request()->header(config('tenancy.header_name', 'X-Tenant'));
	 }
}