<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class SubdomainTenantIDResolver implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {
	 	 
	 	 $host = request()->host();

         $parts = explode('.', $host);

         return $parts[0] ?? null;
	 }
}