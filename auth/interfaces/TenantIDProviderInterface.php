<?php
namespace SaQle\Auth\Interfaces;

interface TenantIDProviderInterface {
     /**
     * Extracts the tenant's ID from the request (cookie, header, etc.).
     * Returns the tenant ID if found or null if not.
     */
     public function tenant_id() : null|int|string;
}
