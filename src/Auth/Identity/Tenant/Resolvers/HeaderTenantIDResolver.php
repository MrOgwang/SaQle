<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\Identity\Tenant\Interfaces\TenantIDResolverInterface;

class HeaderTenantIDResolver implements TenantIDResolverInterface {
	 
	 public function __construct(private string $key){}

	 public function resolve() : null|int|string {
	 	 return request()->header($this->key);
	 }
}