<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\Identity\Tenant\Interfaces\TenantIDResolverInterface;

class AuthUserTenantIDResolver implements TenantIDResolverInterface {

	 public function __construct(private string $key){}

	 public function resolve() : null|int|string {
	 	 $field = $this->key;
         return request()->user?->$field;
	 }
}