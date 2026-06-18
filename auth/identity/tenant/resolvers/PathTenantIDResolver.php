<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class PathTenantIDResolver implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {

	 	 $segment =config('tenancy.path_segment', 1);

         return null;

	 }
}