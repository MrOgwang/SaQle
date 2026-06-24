<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\Identity\Tenant\Interfaces\TenantIDResolverInterface;

class SubdomainTenantIDResolver implements TenantIDResolverInterface {

	 public function __construct(private string $key){}

	 public function resolve() : null|int|string {
	 	 
	 	 $host = request()->host();

         $parts = explode('.', $host);

         return $parts[0] ?? null;
	 }
}