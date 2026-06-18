<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class AuthUserTenantIDResolver implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {
         return request()->user?->tenant_id;
	 }
}