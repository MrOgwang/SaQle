<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class DomainTenantIDProvider implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {
	 	  return request()->host();
	 }
}