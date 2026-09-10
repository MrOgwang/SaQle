<?php

namespace SaQle\Build\Commands;

use SaQle\Core\Support\Db;
use SaQle\Commons\File;
use SaQle\Core\Support\Cli;
use SaQle\Console\Command;
use SaQle\Console\CommandContext;
use SaQle\Console\Signature\Signature;
use SaQle\Core\Migration\Models\Migration;

/**
 * Migration runner.
 *
 * Responsibilities:
 *
 * 1. Ensure the system database exists.
 * 2. Ensure migration tracking exists on every physical database.
 * 3. Execute system migrations once.
 * 4. Execute application migrations:
 *      - once on the default application DB when tenancy is disabled
 *      - once per tenant DB when tenancy is enabled
 *
 * Migration files describe LOGICAL databases.
 *
 * The migration runner resolves those logical databases into
 * physical migration targets.
 */
class Migrate extends Command
{
    private string $migrations_folder;

    private string $system_migrations_folder;

    private string $application_migrations_folder;

    /**
     * Logical system connection.
     */
    private string $system_connection = 'default.system';

    /**
     * Logical default application connection.
     */
    private string $application_connection = 'default.default';


    public function __construct()
    {
        $base_path = config('base_path');

        $this->migrations_folder = path_join([
            $base_path,
            'src',
            'Databases',
            'Migrations'
        ]);

        $this->system_migrations_folder = path_join([
            $this->migrations_folder,
            'System'
        ]);

        /*
         * Prefer Application.
         *
         * If the application is still using the old Tenant folder,
         * fall back to Tenant so existing projects continue to work.
         */
        $application_folder = path_join([
            $this->migrations_folder,
            'Application'
        ]);

        $legacy_tenant_folder = path_join([
            $this->migrations_folder,
            'Tenant'
        ]);

        if (is_dir($application_folder)) {
            $this->application_migrations_folder = $application_folder;
        } else {
            $this->application_migrations_folder = $legacy_tenant_folder;
        }
    }


    public function signature(): Signature
    {
        return Signature::make();
    }


    /**
     * Entry point.
     */
    public function handle(CommandContext $context): int
    {
        $tenancy_enabled = (bool) config(
            'tenancy.enabled',
            false
        );

        Cli::print("\n========================================");
        Cli::print(" SaQle Database Migration");
        Cli::print("========================================\n");

        /*
         * ---------------------------------------------------------
         * 1. SYSTEM DATABASE
         * ---------------------------------------------------------
         */

        Cli::print("Preparing system database...\n");

        [$system_connection, $system_driver] =
            $this->ensure_database($this->system_connection);

        if (!$system_connection || !$system_driver) {
            Cli::print("Unable to prepare system database.\n");
            return 1;
        }

        /*
         * The system migrations table must exist before we can
         * record system migration state.
         */
        if (!$this->ensure_migrations_table(
            $system_connection,
            $system_driver
        )) {
            Cli::print("Unable to prepare system migrations table.\n");
            return 1;
        }

        /*
         * ---------------------------------------------------------
         * 2. SYSTEM MIGRATIONS
         * ---------------------------------------------------------
         */

        Cli::print("\nMigrating system database...\n");

        $system_result = $this->run_system_migrations();

        if (!$system_result) {
            Cli::print("System migration failed.\n");
            return 1;
        }

        Cli::print("\nSystem database migration complete.\n");


        /*
         * ---------------------------------------------------------
         * 3. APPLICATION DATABASES
         * ---------------------------------------------------------
         */

        if ($tenancy_enabled) {

            Cli::print("\nTenancy is ENABLED.\n");
            Cli::print("Resolving tenants...\n");

            $tenants = $this->get_tenants();

            if (!$tenants) {
                Cli::print("No tenants found.\n");
                Cli::print("Nothing else to migrate.\n");

                return 0;
            }

            Cli::print(
                count($tenants) .
                " tenant(s) found.\n"
            );

            foreach ($tenants as $tenant) {

                Cli::print(
                    "\n----------------------------------------\n"
                );

                Cli::print(
                    "Migrating tenant: {$tenant->tenant_name}\n"
                );

                if (!$this->run_application_migrations_for_tenant(
                    $tenant
                )) {
                    Cli::print(
                        "Migration failed for tenant: " .
                        "{$tenant->tenant_name}\n"
                    );

                    return 1;
                }

                Cli::print(
                    "Tenant {$tenant->tenant_name} migrated successfully.\n"
                );
            }

        } else {

            Cli::print("\nTenancy is DISABLED.\n");
            Cli::print(
                "Migrating default application database...\n"
            );

            if (!$this->run_application_migrations_for_connection(
                $this->application_connection
            )) {
                Cli::print(
                    "Application database migration failed.\n"
                );

                return 1;
            }

            /*
             * In single-database mode we still want a tenant record
             * because other parts of SaQle may use the tenant abstraction.
             */
            $this->ensure_default_tenant();
        }


        Cli::print("\n========================================");
        Cli::print(" Migration completed successfully!");
        Cli::print("========================================\n");

        return 0;
    }


    /**
     * -------------------------------------------------------------
     * DATABASE PREPARATION
     * -------------------------------------------------------------
     */


    /**
     * Ensure a logical connection has a physical database.
     *
     * Returns:
     *
     * [
     *     connection_name,
     *     driver
     * ]
     */
    private function ensure_database(
        string $connection
    ): array {

        Cli::print(
            "Checking database connection: {$connection}\n"
        );

        /*
         * Confirm that the connection/schema exists.
         */
        Db::get_connection_schema(
            connection_key: $connection
        );

        $driver = Db::using($connection)->driver();

        if (!$driver->check_database_exists()) {

            Cli::print(
                "Database does not exist. Creating it...\n"
            );

            if (!$driver->create_database()) {

                Cli::print(
                    "Unable to create database for {$connection}.\n"
                );

                return [false, false];
            }

            Cli::print("Database created successfully.\n");
        }

        $driver->connect_with_database();

        return [
            $connection,
            $driver
        ];
    }


    /**
     * -------------------------------------------------------------
     * MIGRATION TABLE
     * -------------------------------------------------------------
     */


    /**
     * Ensure the local migrations table exists.
     *
     * IMPORTANT:
     *
     * The migration table belongs to the physical database being
     * migrated.
     *
     * Therefore:
     *
     *     system DB
     *          -> migrations
     *
     *     application DB
     *          -> migrations
     *
     *     tenant A DB
     *          -> migrations
     *
     *     tenant B DB
     *          -> migrations
     */
    private function ensure_migrations_table(
        string $connection,
        $driver
    ): bool {

        if ($driver->table_exists('migrations')) {
            return true;
        }

        Cli::print(
            "Creating migrations table in {$connection}...\n"
        );

        $snapshot_location =
            $this->get_system_migrations_snapshot();

        if (!$snapshot_location) {

            Cli::print(
                "Unable to locate system migration snapshot.\n"
            );

            return false;
        }

        require_once $snapshot_location['path'];

        $snapshot_class =
            $snapshot_location['name'];

        $snapshot =
            new $snapshot_class();

        $field_defs =
            $this->extract_snapshot_field_definitions(
                $snapshot->get_model_fields(),
                'migrations'
            );

        $unique_defs =
            $driver->get_unique_constraint_sqls(
                $snapshot->get_unique_constraints()['migrations']
                ?? []
            );

        $fk_defs =
            $driver->get_fk_constraint_sqls(
                $snapshot->get_fk_constraints()['migrations']
                ?? []
            );

        return $driver->create_table_from_migration(
            'migrations',
            $field_defs,
            $unique_defs,
            $fk_defs
        );
    }


    /**
     * Find the migrations table definition from the system
     * migration snapshot.
     *
     * The snapshot remains the source of truth for the table
     * structure.
     */
    private function get_system_migrations_snapshot(): ?array
    {
        $files = $this->get_migration_files(
            $this->system_migrations_folder
        );

        foreach ($files as $file) {

            $file_name =
                pathinfo($file, PATHINFO_FILENAME);

            $file_path =
                path_join([
                    $this->system_migrations_folder,
                    $file
                ]);

            require_once $file_path;

            if (!class_exists($file_name)) {
                continue;
            }

            $migration = new $file_name();

            $snapshots =
                $migration->snapshots();

            if (
                isset(
                    $snapshots[$this->system_connection]
                )
            ) {
                return $snapshots[
                    $this->system_connection
                ];
            }
        }

        return null;
    }


    /**
     * -------------------------------------------------------------
     * SYSTEM MIGRATIONS
     * -------------------------------------------------------------
     */


    private function run_system_migrations(): bool
    {
        $files = $this->get_migration_files(
            $this->system_migrations_folder
        );

        foreach ($files as $file) {

            if (!$this->run_migration_file(
                'System',
                $file,
                $this->system_connection
            )) {
                return false;
            }
        }

        return true;
    }


    /**
     * -------------------------------------------------------------
     * APPLICATION MIGRATIONS
     * -------------------------------------------------------------
     */


    /**
     * Run application migrations against a tenant.
     */
    private function run_application_migrations_for_tenant(
        $tenant
    ): bool {

        /*
         * Convert:
         *
         *     default.default
         *
         * into the physical tenant connection.
         *
         * This uses your existing Db tenant registration mechanism.
         */
        [
            $tenant_connection,
            $tenant_schema
        ] = Db::register_tenant_db(
            $this->application_connection,
            $tenant
        );

        if (!$tenant_connection) {
            Cli::print(
                "Unable to register tenant database.\n"
            );

            return false;
        }

        return $this->run_application_migrations_for_connection(
            $tenant_connection
        );
    }


    /**
     * Run application migrations against one physical database.
     */
    private function run_application_migrations_for_connection(
        string $connection
    ): bool {

        Cli::print(
            "Preparing application database: {$connection}\n"
        );

        [
            $resolved_connection,
            $driver
        ] = $this->ensure_database($connection);

        if (
            !$resolved_connection ||
            !$driver
        ) {
            return false;
        }

        /*
         * Every application database gets its own migration
         * history.
         */
        if (!$this->ensure_migrations_table(
            $resolved_connection,
            $driver
        )) {
            return false;
        }

        $files = $this->get_migration_files(
            $this->application_migrations_folder
        );

        foreach ($files as $file) {

            if (!$this->run_migration_file(
                'Application',
                $file,
                $resolved_connection
            )) {
                return false;
            }
        }

        return true;
    }


    /**
     * -------------------------------------------------------------
     * MIGRATION EXECUTION
     * -------------------------------------------------------------
     */


    /**
     * Execute one migration against one physical database.
     */
    private function run_migration_file(
        string $type,
        string $file,
        string $physical_connection
    ): bool {

        $folder =
            $type === 'System'
                ? $this->system_migrations_folder
                : $this->application_migrations_folder;

        $file_name =
            pathinfo($file, PATHINFO_FILENAME);

        $file_path =
            path_join([
                $folder,
                $file
            ]);

        if (!file_exists($file_path)) {
            return true;
        }

        Cli::print(
            "\nScanning migration: {$file_name}\n"
        );

        require_once $file_path;

        if (!class_exists($file_name)) {

            Cli::print(
                "Migration class {$file_name} not found.\n"
            );

            return false;
        }

        $migration =
            new $file_name();

        $migration_name =
            $migration->get_migration_name();

        $migration_timestamp =
            $migration->get_migration_timestamp();

        $snapshots =
            $migration->snapshots();

        /*
         * A migration with no snapshots is allowed.
         *
         * This can be useful for data-only migrations or other
         * migration types later.
         */
        if (!$snapshots) {

            Cli::print(
                "No database schemas affected. Skipping.\n"
            );

            return true;
        }

        /*
         * Check local migration history.
         */
        $record =
            $this->get_migration_record(
                $physical_connection,
                $migration_name,
                $migration_timestamp
            );

        if (
            $record &&
            (int) $record->is_migrated === 1
        ) {

            Cli::print(
                "Already migrated. Skipping.\n"
            );

            return true;
        }

        /*
         * Execute migration.
         */
        $up_operations =
            $migration->up();

        /*
         * A migration describes logical connections.
         *
         * We need to find which snapshot corresponds to the
         * physical database we are currently migrating.
         */
        foreach ($snapshots as $logical_connection => $snapshot) {

            /*
             * System migrations only apply to the system DB.
             */
            if ($type === 'System') {

                if (
                    $logical_connection !==
                    $this->system_connection
                ) {
                    continue;
                }

                $target_connection =
                    $this->system_connection;

            } else {

                /*
                 * Application migrations normally target:
                 *
                 *     default.default
                 *
                 * But the physical connection might be:
                 *
                 *     default.tenant_abc
                 *
                 * Therefore compare against the logical
                 * application connection.
                 */
                if (
                    $logical_connection !==
                    $this->application_connection
                ) {
                    continue;
                }

                $target_connection =
                    $physical_connection;
            }

            if (
                !$this->process_snapshot(
                    $target_connection,
                    $snapshot,
                    $up_operations,
                    $logical_connection
                )
            ) {
                return false;
            }
        }

        /*
         * Migration was successfully applied to this physical
         * database.
         */
        $this->mark_migration_as_migrated(
            $physical_connection,
            $record->migration_id
        );

        Cli::print(
            "Migration {$migration_name} completed.\n"
        );

        return true;
    }


    /**
     * Apply the operations for a snapshot to a physical database.
     */
    private function process_snapshot(
        string $physical_connection,
        array $snapshot_location,
        array $up_operations,
        string $logical_connection
    ): bool {

        $snapshot_path =
            $snapshot_location['path'];

        $snapshot_class =
            $snapshot_location['name'];

        /*
         * Make sure the physical connection is registered.
         */
        Db::get_connection_schema(
            connection_key: $physical_connection
        );

        require_once $snapshot_path;

        if (!class_exists($snapshot_class)) {

            Cli::print(
                "Snapshot class {$snapshot_class} not found.\n"
            );

            return false;
        }

        $snapshot =
            new $snapshot_class();

        $driver =
            Db::using($physical_connection)->driver();

        $driver->connect_with_database();

        /*
         * The migration operations are keyed using the logical
         * connection, NOT the physical tenant connection.
         *
         * Example:
         *
         *     up()['default.default']
         *
         * is applied to:
         *
         *     default.tenant_abc
         */
        $operations =
            $up_operations[$logical_connection] ?? [];

        foreach ($operations as $operation) {

            $result =
                $this->process_up_operation(
                    $operation,
                    $driver,
                    $snapshot
                );

            if ($result === false) {
                return false;
            }
        }

        return true;
    }


    /**
     * -------------------------------------------------------------
     * MIGRATION RECORDS
     * -------------------------------------------------------------
     */


    /**
     * Retrieve migration history from the physical database.
     */
    private function get_migration_record(
        string $connection,
        string $migration_name,
        int $migration_timestamp
    ) {

        $record =
            Migration::using($connection)
                ->get()
                ->where(
                    'migration_name__eq',
                    $migration_name
                )
                ->where(
                    'migration_timestamp__eq',
                    $migration_timestamp
                )
                ->first_or_null();

        if ($record) {
            return $record;
        }

        return Migration::using($connection)
            ->create([
                'migration_name' =>
                    $migration_name,

                'migration_timestamp' =>
                    $migration_timestamp,

                'is_migrated' =>
                    0,

                'type' =>
                    'database'
            ])
            ->now();
    }


    /**
     * Mark migration complete in the database being migrated.
     */
    private function mark_migration_as_migrated(
        string $connection,
        string $migration_id
    ): void {

        Migration::using($connection)
            ->update([
                'is_migrated' => 1
            ])
            ->where(
                'migration_id',
                $migration_id
            )
            ->now();
    }


    /**
     * -------------------------------------------------------------
     * TENANTS
     * -------------------------------------------------------------
     */


    private function get_tenants(): array
    {
        $tenant_model =
            config('tenancy.model_class');

        if (!$tenant_model) {
            return [];
        }

        return $tenant_model::using(
            system_connection()
        )
            ->get()
            ->all()
            ->items();
    }


    /**
     * In non-tenant mode SaQle still maintains one logical tenant.
     */
    private function ensure_default_tenant(): void
    {
        $tenant_model =
            config('tenancy.model_class');

        if (!$tenant_model) {
            return;
        }

        $latest_tenant =
            $tenant_model::using(
                system_connection()
            )
                ->get()
                ->order(
                    fields: ['created_at'],
                    direction: 'DESC'
                )
                ->limit(1)
                ->first_or_null();

        if ($latest_tenant) {
            return;
        }

        $tenant_model::using(
            system_connection()
        )
            ->create([
                'tenant_name' =>
                    config('app.name')
            ])
            ->now();
    }


    /**
     * -------------------------------------------------------------
     * OPERATION HANDLERS
     * -------------------------------------------------------------
     */


    private function process_up_operation(
        array $operation,
        $driver,
        $snapshot
    ) {

        return match ($operation['action']) {

            'create_table' =>
                $this->create_table(
                    $operation,
                    $driver,
                    $snapshot
                ),

            'drop_table' =>
                $this->drop_table(
                    $operation,
                    $driver
                ),

            'add_columns' =>
                $this->add_columns(
                    $operation,
                    $driver
                ),

            'drop_columns' =>
                $this->drop_columns(
                    $operation,
                    $driver
                ),

            'update_unique' =>
                $this->update_unique(
                    $operation,
                    $driver
                ),

            'rename_table' =>
                $this->rename_table(
                    $operation,
                    $driver
                ),

            default => throw new \RuntimeException(
                "Unknown migration operation: " .
                $operation['action']
            )
        };
    }


    private function create_table(
        array $operation,
        $driver,
        $snapshot
    ) {

        $table_name =
            $operation['params']['name'];

        Cli::print(
            "Creating table: {$table_name}\n"
        );

        /*
         * Don't attempt to recreate an existing table.
         */
        if ($driver->table_exists($table_name)) {

            Cli::print(
                "Table {$table_name} already exists. Skipping.\n"
            );

            return true;
        }

        $field_defs =
            $this->extract_snapshot_field_definitions(
                $snapshot->get_model_fields(),
                $table_name
            );

        $unique_defs =
            $driver->get_unique_constraint_sqls(
                $snapshot->get_unique_constraints()[
                    $table_name
                ] ?? []
            );

        $fk_defs =
            $driver->get_fk_constraint_sqls(
                $snapshot->get_fk_constraints()[
                    $table_name
                ] ?? []
            );

        $created =
            $driver->create_table_from_migration(
                $table_name,
                $field_defs,
                $unique_defs,
                $fk_defs
            );

        if (!$created) {

            Cli::print(
                "Table {$table_name} creation failed.\n"
            );

            return false;
        }

        Cli::print(
            "Table {$table_name} created.\n"
        );

        return true;
    }


    private function drop_table(
        array $operation,
        $driver
    ) {

        $table_name =
            $operation['params']['name'];

        Cli::print(
            "Dropping table: {$table_name}\n"
        );

        if (!$driver->table_exists($table_name)) {

            Cli::print(
                "Table {$table_name} does not exist. Skipping.\n"
            );

            return true;
        }

        $dropped =
            $driver->drop_table($table_name);

        if (!$dropped) {

            Cli::print(
                "Table {$table_name} deletion failed.\n"
            );

            return false;
        }

        Cli::print(
            "Table {$table_name} deleted.\n"
        );

        return true;
    }


    private function rename_table(
        array $operation,
        $driver
    ) {

        $old_name =
            $operation['params']['old'];

        $new_name =
            $operation['params']['new'];

        Cli::print(
            "Renaming {$old_name} to {$new_name}\n"
        );

        return $driver->rename_table(
            $old_name,
            $new_name
        );
    }


    private function add_columns(
        array $operation,
        $driver
    ) {

        $table_name =
            $operation['params']['name'];

        Cli::print(
            "Adding columns to {$table_name}\n"
        );

        return $driver->add_columns(
            $table_name,
            $operation['params']['columns']
        );
    }


    private function drop_columns(
        array $operation,
        $driver
    ) {

        $table_name =
            $operation['params']['name'];

        Cli::print(
            "Dropping columns from {$table_name}\n"
        );

        return $driver->drop_columns(
            $table_name,
            $operation['params']['columns']
        );
    }


    private function update_unique(
        array $operation,
        $driver
    ) {

        $table_name =
            $operation['params']['name'];

        Cli::print(
            "Updating unique constraints on {$table_name}\n"
        );

        return $driver->add_unique_constraints(
            $table_name,
            $operation['unique'],
            $operation['prev_unique']
        );
    }


    /**
     * -------------------------------------------------------------
     * SNAPSHOT HELPERS
     * -------------------------------------------------------------
     */


    private function extract_snapshot_field_definitions(
        array $schema,
        string $table
    ): array {

        if (!isset($schema[$table])) {
            return [];
        }

        return array_filter(
            array_map(
                fn($field) => $field['def'],
                $schema[$table]
            )
        );
    }


    /**
     * -------------------------------------------------------------
     * FILE HELPERS
     * -------------------------------------------------------------
     */


    private function get_migration_files(
        string $folder
    ): array {

        if (!is_dir($folder)) {
            return [];
        }

        $files =
            File::scandir(
                path: $folder,
                exts: ['php']
            );

        $ordered = [];

        foreach ($files as $file) {

            $name =
                pathinfo(
                    $file,
                    PATHINFO_FILENAME
                );

            $parts =
                explode('_', $name);

            /*
             * Expected:
             *
             * System_Migration_20260907162626_Initial
             *
             * or
             *
             * Tenant_Migration_20260907162626_Initial
             */
            if (!isset($parts[2])) {
                continue;
            }

            $timestamp =
                $parts[2];

            $ordered[$timestamp] =
                $file;
        }

        ksort($ordered);

        return array_values($ordered);
    }
}