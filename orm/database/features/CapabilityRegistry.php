<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Features;

final class CapabilityRegistry {
    public static function get(string $engine, array $version): array {
        return match ($engine) {

            // ---------------- MySQL ----------------
            'mysql' => [
                DatabaseFeatures::RETURNING                => false,
                DatabaseFeatures::UPSERT                   => true,
                DatabaseFeatures::NO_OP_UPDATE             => true,
                DatabaseFeatures::MULTI_ROW_INSERT         => true,
                DatabaseFeatures::TRANSACTIONS             => true,
                DatabaseFeatures::SAVEPOINTS               => true,
                DatabaseFeatures::WINDOW_FUNCTIONS         => VersionParser::at_least($version, 8, 0),
                DatabaseFeatures::COMMON_TABLE_EXPRESSIONS => VersionParser::at_least($version, 8, 0),
                DatabaseFeatures::JSON_TYPE                => true,
                DatabaseFeatures::GENERATED_COLUMNS        => VersionParser::at_least($version, 5, 7),
                DatabaseFeatures::CHECK_CONSTRAINTS        => VersionParser::at_least($version, 8, 0),
                DatabaseFeatures::PARTIAL_INDEXES          => false,
                DatabaseFeatures::FULL_TEXT_SEARCH         => true,
                DatabaseFeatures::DEFERRABLE_CONSTRAINTS   => false,
                DatabaseFeatures::LOCKING_READS            => true,
            ],

            // ---------------- MariaDB ----------------
            'mariadb' => [
                DatabaseFeatures::RETURNING                => VersionParser::at_least($version, 10, 5),
                DatabaseFeatures::UPSERT                   => true,
                DatabaseFeatures::NO_OP_UPDATE             => true,
                DatabaseFeatures::MULTI_ROW_INSERT         => true,
                DatabaseFeatures::TRANSACTIONS             => true,
                DatabaseFeatures::SAVEPOINTS               => true,
                DatabaseFeatures::WINDOW_FUNCTIONS         => VersionParser::at_least($version, 10, 2),
                DatabaseFeatures::COMMON_TABLE_EXPRESSIONS => VersionParser::at_least($version, 10, 2),
                DatabaseFeatures::JSON_TYPE                => true,
                DatabaseFeatures::GENERATED_COLUMNS        => VersionParser::at_least($version, 10, 2),
                DatabaseFeatures::CHECK_CONSTRAINTS        => VersionParser::at_least($version, 10, 2),
                DatabaseFeatures::PARTIAL_INDEXES          => false,
                DatabaseFeatures::FULL_TEXT_SEARCH         => true,
                DatabaseFeatures::DEFERRABLE_CONSTRAINTS   => false,
                DatabaseFeatures::LOCKING_READS            => true,
            ],

            // ---------------- PostgreSQL ----------------
            'postgres' => [
                DatabaseFeatures::RETURNING                => true,
                DatabaseFeatures::UPSERT                   => VersionParser::at_least($version, 9, 5),
                DatabaseFeatures::NO_OP_UPDATE             => true,
                DatabaseFeatures::MULTI_ROW_INSERT         => true,
                DatabaseFeatures::TRANSACTIONS             => true,
                DatabaseFeatures::SAVEPOINTS               => true,
                DatabaseFeatures::WINDOW_FUNCTIONS         => true,
                DatabaseFeatures::COMMON_TABLE_EXPRESSIONS => true,
                DatabaseFeatures::JSON_TYPE                => true,
                DatabaseFeatures::GENERATED_COLUMNS        => VersionParser::at_least($version, 12),
                DatabaseFeatures::CHECK_CONSTRAINTS        => true,
                DatabaseFeatures::PARTIAL_INDEXES          => true,
                DatabaseFeatures::FULL_TEXT_SEARCH         => true,
                DatabaseFeatures::DEFERRABLE_CONSTRAINTS   => true,
                DatabaseFeatures::LOCKING_READS            => true,
            ],

            // ---------------- SQLite ----------------
            'sqlite' => [
                DatabaseFeatures::RETURNING                => VersionParser::at_least($version, 3, 35),
                DatabaseFeatures::UPSERT                   => VersionParser::at_least($version, 3, 24),
                DatabaseFeatures::NO_OP_UPDATE             => true,
                DatabaseFeatures::MULTI_ROW_INSERT         => true,
                DatabaseFeatures::TRANSACTIONS             => true,
                DatabaseFeatures::SAVEPOINTS               => true,
                DatabaseFeatures::WINDOW_FUNCTIONS         => VersionParser::at_least($version, 3, 25),
                DatabaseFeatures::COMMON_TABLE_EXPRESSIONS => VersionParser::at_least($version, 3, 8),
                DatabaseFeatures::JSON_TYPE                => false,
                DatabaseFeatures::GENERATED_COLUMNS        => VersionParser::at_least($version, 3, 31),
                DatabaseFeatures::CHECK_CONSTRAINTS        => true,
                DatabaseFeatures::PARTIAL_INDEXES          => true,
                DatabaseFeatures::FULL_TEXT_SEARCH         => true,
                DatabaseFeatures::DEFERRABLE_CONSTRAINTS   => false,
                DatabaseFeatures::LOCKING_READS            => false,
            ],

            default => [],
        };
    }
}
