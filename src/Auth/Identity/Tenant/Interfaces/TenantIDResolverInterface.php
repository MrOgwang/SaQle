<?php
namespace SaQle\Auth\Identity\Tenant\Interfaces;

interface TenantIDResolverInterface {
     /**
     * Extracts the tenant's ID from the request (cookie, header, etc.).
     * Returns the tenant ID if found or null if not.
     */
     public function resolve() : null|int|string;
}
