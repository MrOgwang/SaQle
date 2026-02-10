<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Features;

final class VersionParser {
    public static function normalize(string $version_string): array {
        preg_match('/(\d+)\.(\d+)(?:\.(\d+))?/', $version_string, $m);

        return [
            'major' => (int)($m[1] ?? 0),
            'minor' => (int)($m[2] ?? 0),
            'patch' => (int)($m[3] ?? 0),
        ];
    }

    public static function at_least(array $v, int $maj, int $min = 0): bool {
        return ($v['major'] > $maj)
            || ($v['major'] === $maj && $v['minor'] >= $min);
    }
}
