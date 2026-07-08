<?php
namespace SaQle\Auth\Identity\Tenant\Interfaces;

interface TenantProviderInterface {
      public function find(string|int $id): ?TenantInterface;
}