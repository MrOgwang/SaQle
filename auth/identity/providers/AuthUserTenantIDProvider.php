<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class AuthUserTenantIDProvider implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {
         return request()->user?->tenant_id;
	 }
}