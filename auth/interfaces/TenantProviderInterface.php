<?php

namespace SaQle\Auth\interfaces;

use SaQle\Auth\Interfaces\TenantInterface;

interface TenantProviderInterface {
      public function find(string|int $id): ?TenantInterface;
}