<?php
namespace SaQle\Orm\Database\Drivers;

use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Connection\ConnectionManager;
use SaQle\Orm\Database\Features\DatabaseFeatures;
use SaQle\Orm\Database\Features\FeatureDetector;

abstract class DbDriver {

    protected ConnectionConfig $config;
    protected $connection = null;

    /**
     * Connection key used for event dispatching
     */
    protected string $connection_key = "";

    /**
     * Cached database version string
     */
    protected string $version = '';

    /**
     * Cached feature detector
     */
    protected FeatureDetector $features;

    public function __construct(ConnectionConfig $config){
        $this->config = $config;

        // Establish low-level connection (no DB selected)
        $this->connect_without_database();

        // Resolve version ONCE
        $this->version = $this->resolve_version();

        // Build feature detector ONCE
        $this->features = new FeatureDetector($this->version);
    }

    // ------------------------------------------------------------------
    // Metadata
    // ------------------------------------------------------------------

    public function name() : string {
        return $this->config->get_driver();
    }

    public function get_version() : string {
        return $this->version;
    }

    public function get_config(){
        return $this->config;
    }

    public function get_connection(){
        return $this->connection;
    }

    public function get_connection_key(){
        return $this->connection_key;
    }

    // ------------------------------------------------------------------
    // Feature support (delegated to FeatureDetector)
    // ------------------------------------------------------------------

    public function supports_window_functions() : bool {
        return $this->features->supports(DatabaseFeatures::WINDOW_FUNCTIONS);
    }

    public function supports_returning() : bool {
        return $this->features->supports(DatabaseFeatures::RETURNING);
    }

    public function supports_cte() : bool {
        return $this->features->supports(DatabaseFeatures::COMMON_TABLE_EXPRESSIONS);
    }

    public function supports_json() : bool {
        return $this->features->supports(DatabaseFeatures::JSON_TYPE);
    }

    // Optional generic hook (future-proof)
    public function supports(string $feature) : bool {
        return $this->features->supports($feature);
    }

    // ------------------------------------------------------------------
    // Version resolution (runs ONCE)
    // ------------------------------------------------------------------

    protected function resolve_version() : string
    {
        //1. Prefer explicitly configured version (zero SQL cost)
        $options = $this->config->get_options();
        if (!empty($options['version'])) {
            return (string)$options['version'];
        }

        //2. Fallback: query database ONCE
        try {
            $stmt = $this->connection->query('SELECT VERSION()');
            return (string)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            // Absolute fallback: unknown version
            return 'unknown';
        }
    }

    // ------------------------------------------------------------------
    // Connection handling
    // ------------------------------------------------------------------

    protected function connect_without_database(){
        [$c, $k] = ConnectionManager::get($this->config, false);
        $this->connection = $c;
        $this->connection_key = $k;
    }

    public function connect_with_database(){
        [$c, $k] = ConnectionManager::get($this->config, true);
        $this->connection = $c;
        $this->connection_key = $k;
    }

    // ------------------------------------------------------------------
    // SQL execution
    // ------------------------------------------------------------------

    public function execute($sql, $data = null){
        $statement = $this->connection->prepare($sql);
        $response  = $statement->execute($data);
        return ['statement' => $statement, 'response' => $response];
    }

    // ------------------------------------------------------------------
    // Schema helpers
    // ------------------------------------------------------------------

    public function get_unique_constraint_sqls(array $unique_snapshot){
        $unique_sqls = [];
        foreach($unique_snapshot as $constraint_name => $constraint_columns){
            $unique_sqls[] =
                "CONSTRAINT ".$constraint_name." UNIQUE (".implode(', ', $constraint_columns).")";
        }
        return $unique_sqls;
    }

    // ------------------------------------------------------------------
    // Abstract schema + DDL operations
    // ------------------------------------------------------------------

    abstract public function check_database_exists() : bool;
    abstract public function create_database();
    abstract public function drop_table(string $table);
    abstract protected function check_column_exists(string $table, string $column) : bool;
    abstract public function add_columns(string $table, array $columns);
    abstract public function drop_columns(string $table, array $columns);
    abstract public function add_unique_constraints(
        string $table,
        array $new_constraints,
        array $previous_constraints = []
    );
    abstract public function drop_unique_constraints(string $table, array $constraints = []);
    abstract protected function resolve_db_column_type(ColumnType $type, object $context) : string;
    abstract public function translate_field_definition(?object $def = null) : string;
    abstract public function create_table_from_migration(
        string $table,
        array $column_sqls,
        array $unique_sqls = [],
        bool $temporary = false
    );
    abstract public function create_table_from_model(
        string $table,
        string $model_class,
        bool $temporary = false
    );
}
