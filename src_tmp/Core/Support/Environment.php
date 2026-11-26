<?php

namespace SaQle\Core\Support;

enum Environment : string {
     case DEVELOPMENT = 'development';
     case TESTING = 'testing';
     case STAGING = 'staging';
     case PRODUCTION = 'production';
     case TUNNEL = 'tunnel';

     public static function current(): self {
         return self::from(config('environment'));
     }

     public function is_development(): bool {
         return $this === self::DEVELOPMENT;
     }

     public function is_testing(): bool {
         return $this === self::TESTING;
     }

     public function is_staging(): bool {
         return $this === self::STAGING;
     }

     public function is_production(): bool {
         return $this === self::PRODUCTION;
     }

     public function is_tunnel(): bool {
         return $this === self::TUNNEL;
     }

     public static function from_string(string $environment, ?self $default = null): ?self {
         return self::tryFrom(strtolower(trim($environment))) ?? $default;
     }

     public static function values(): array {
         return array_column(self::cases(), 'value');
     }
}