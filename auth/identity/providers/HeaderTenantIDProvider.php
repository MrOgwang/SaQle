<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class HeaderTenantIDProvider implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {
	 	 return request()->header(config('tenancy.header_name', 'X-Tenant'));
	 }
}