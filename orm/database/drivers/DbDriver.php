<?php
namespace SaQle\Orm\Database\Drivers;

use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Connection\Connection;

abstract class DbDriver {

	 protected ConnectionConfig $config;
	 protected $connection = null;

	 public function __construct(ConnectionConfig $config){
	 	 $this->config = $config;
	 	 $this->connect_without_database();
	 }

     //return the driver name
	 public function name() : string {
	 	 return $this->config->get_driver();
	 }

	 //return the driver version
     public function get_version() : string {
     	return $this->config->get_options()['version'] ?? "";
     }

     //whether driver supports window functions
     abstract public function supports_window_functions() : bool;

     //whether driver supports returning 
     abstract public function supports_returning() : bool;
     
     //whether driver supports cte
     abstract public function supports_cte() : bool;
     
     //whether driver supports json
     abstract public function supports_json() : bool;

     //check if database exists
     abstract public function check_database_exists() : bool;

     //create a database
	 abstract public function create_database();

	 //drop a table
	 abstract public function drop_table(string $table);

	 //check if a column exists
	 abstract protected function check_column_exists(string $table, string $column) : bool;

	 /**
      * Columns here comes in the form of associative array where column name is the key and column definition is the value.
      * Example: ['user_id' => 'user_id TEXT NOT NULL']
      * */
	 abstract public function add_columns(string $table, array $columns);

	 /**
      * Columns here comes in the form of associative array where column name is the key and column definition is the value.
      * Example: ['user_id' => 'user_id TEXT NOT NULL']
      * */
	 abstract public function drop_columns(string $table, array $columns);

	 /**
      * Add unique constrains to the table. Constraints is an associative array where the key is the constraint name
      * and the value is an array of columns that are unique.
      * */
     abstract public function add_unique_constraints(string $table, array $new_constraints, array $previous_constraints = []);

	 /**
      * Drop unique constrains from the table. Constraints is an associative array where the key is the constraint name
      * and the value is an array of columns that are unique.
      * */
     abstract public function drop_unique_constraints(string $table, array $constraints = []);

     /**
      * Given a framework field type, resolve te actual database type for that
      * field type
      * */
     abstract protected function resolve_db_column_type(ColumnType $type, object $context) : string;

     /**
      * Translate a framework field definiton to sql statement
      * */
     abstract public function translate_field_definition(?object $def = null) : string;

     /**
      * Create a database table from migration
      * */
     abstract public function create_table_from_migration(string $table, array $column_sqls, array $unique_sqls = [], bool $temporary = false);

     //create table from model class
     abstract public function create_table_from_model(string $table, string $model_class, bool $temporary = false);

     //connect to database server without a database
     protected function connect_without_database(){
     	 $params = $this->config->to_array();
     	 $params['database'] = "";
     	 $this->connection = resolve(Connection::class, $params);
     }

     //connect to the database server with a database
     public function connect_with_database(){
     	 $this->connection = resolve(Connection::class, $this->config->to_array());
     }

     //excecute sql statements
     protected function execute($sql, $data = null){
	 	 $statement = $this->connection->prepare($sql);
	     $response  = $statement->execute($data);
	     return ['statement' => $statement, 'response' => $response];
	 }

	 public function get_unique_constraint_sqls(array $unique_snapshot){
     	 $unique_sqls = [];
     	 foreach($unique_snapshot as $constraint_name => $constraint_columns){
     	 	 $unique_sqls[] = "CONSTRAINT ".$constraint_name." UNIQUE (".implode(', ', $constraint_columns).")";
     	 }

     	 return $unique_sqls;
     }

}

