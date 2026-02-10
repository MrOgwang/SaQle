<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Features;

final class EngineDetector {
     public static function detect(string $version_string): string {
        $v = strtolower($version_string);

        if (str_contains($v, 'mariadb')) return 'mariadb';
        if (str_contains($v, 'postgres')) return 'postgres';
        if (str_contains($v, 'sqlite')) return 'sqlite';
        if (str_contains($v, 'oracle')) return 'oracle';
        if (str_contains($v, 'sql server') || str_contains($v, 'microsoft')) return 'sqlserver';
        if (str_contains($v, 'cockroach')) return 'cockroach';
        if (str_contains($v, 'aurora')) return 'aurora';
        if (str_contains($v, 'mysql')) return 'mysql';

        return 'unknown';
     }
}
