<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class SubdomainTenantIDProvider implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {
	 	 
	 	 $host = request()->host();

         $parts = explode('.', $host);

         return $parts[0] ?? null;
	 }
}