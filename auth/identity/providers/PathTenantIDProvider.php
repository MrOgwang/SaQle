<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\interfaces\TenantIDProviderInterface;

class PathTenantIDProvider implements TenantIDProviderInterface {
	 public function tenant_id() : null|int|string {

	 	 $segment =config('tenancy.path_segment', 1);

         return null;

	 }
}