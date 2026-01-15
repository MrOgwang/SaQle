<?php

namespace SaQle\Routes\Canonical;

final class CanonicalRedirect{
     public function __construct(
        public readonly string $location,
        public readonly int $status = 308
     ) {}
}
