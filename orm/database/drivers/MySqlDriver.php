<?php
namespace SaQle\Orm\Database\Drivers;

use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Operations\Crud\TableCreateOperation;
use SaQle\Orm\Connection\Connection;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class MySqlDriver extends DbDriver {
	 
	 public function __construct(ConnectionConfig $config){
	 	 parent::__construct($config);
	 }

	 public function check_database_exists() : bool {
         $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
         $data = [$this->config->get_database()];
         $statement = $this->execute($sql, $data)['statement'];
         $object = $statement->fetchObject(); 

         return $object ? true : false;
     }

	 public function create_database(){
		 $char_set = $this->config->get_charset();
		 $collation = $this->config->get_collation();
		 $db_name = $this->config->get_database();
		 $sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET $char_set COLLATE $collation";

		 return $this->execute($sql)['response'];
	 }

	 public function drop_table(string $table){
     	 $sql = "DROP TABLE IF EXISTS $table";
		 
		 return $this->execute($sql)['response'];
     }

     protected function check_column_exists(string $table, string $column) : bool {
     	 $sql = "SELECT IF(count(*) = 1, 'Exist','Not Exist') AS result FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?";
         $data = [$this->config->get_database(), $table, $column];
         $statement = $this->execute($sql, $data)['statement'];
         $object = $statement->fetchObject(); 
         
         return $object->result == "Exist" ? true : false;
     }

     /**
      * Columns here comes in the form of associative array where column name is the key and column definition is the value.
      * Example: ['user_id' => 'user_id TEXT NOT NULL']
      * */
     public function add_columns(string $table, array $columns){
     	 /**
     	  * Only add columns that are not presently existing on this table.
     	  * */
     	 $columns_to_add = [];
     	 foreach($columns as $col_name => $col_def){
     	 	if(!$this->check_column_exists($table, $col_name)){
     	 		$columns_to_add[] = $col_def;
     	 	}
     	 }
     	
     	 $definitions = array_map(function($c){
     	 	return 'ADD COLUMN '.$c;
     	 }, $columns_to_add );

     	 $definitions = implode(", ", $definitions);
     	 $sql = "ALTER TABLE $table $definitions";
		 $data = null;
		 return $this->execute($sql, $data)['response'];
     }

     /**
      * Columns here comes in the form of associative array where column name is the key and column definition is the value.
      * Example: ['user_id' => 'user_id TEXT NOT NULL']
      * */
     public function drop_columns(string $table, array $columns){
     	 /**
     	  * Only drop columns that are presently existing on this table.
     	  * */
     	 $columns_to_drop = [];
     	 foreach($columns as $col_name => $col_def){
     	 	if($this->check_column_exists($table, $col_name)){
     	 		$columns_to_drop[] = $col_name;
     	 	}
     	 }

     	 $definitions = array_map(function($c){
     	 	return "DROP COLUMN ".$c;
     	 }, $columns_to_drop);

     	 $definitions = implode(", ", $definitions);
     	 $sql = "ALTER TABLE $table $definitions";
		 $data = null;
		 return $this->execute($sql, $data)['response'];
     }

     /**
      * Add unique constrains to the table. Constraints is an associative array where the key is the constraint name
      * and the value is an array of columns that are unique.
      * */
     public function add_unique_constraints(string $table, array $new_constraints, array $previous_constraints = []){
     	 
     	 //first remove previous constraints
     	 $this->drop_unique_constraints($table, $previous_constraints);

     	 foreach($new_constraints as $name => $columns){
     	 	 $sql = "ALTER TABLE $table ADD CONSTRAINT ".$name." UNIQUE (".implode(", ", $columns).")";
     	 	 $this->execute($sql);
     	 }
     }

     /**
      * Drop unique constrains from the table. Constraints is an associative array where the key is the constraint name
      * and the value is an array of columns that are unique.
      * */
     public function drop_unique_constraints(string $table, array $constraints = []){
     	 foreach($constraints as $name => $columns){
     	 	 $sql = "ALTER TABLE $table DROP INDEX ".$name;
     	 	 $this->execute($sql);
     	 }
     }

     /**
      * Given a framework field type, resolve te actual database type for that
      * field type
     * */
     protected function resolve_db_column_type(ColumnType $type, object $context) : string {

     	 if(ColumnType::INTEGER === $type){
     	 	 return match($context->size){
     	 	 	 'regular' => 'INT',
     	 	 	 'big'     => 'BIGINT',
     	 	 	 'medium'  => 'MEDIUMINT',
     	 	 	 'small'   => 'SMALLINT',
     	 	 	 'tiny'    => 'TINYINT'
     	 	 };
     	 }

     	 if(ColumnType::CHAR === $type){ //some char types may miss a length!
     	 	 $islength = $context->length ?? null;
     	 	 if($islength)
     	 	     return "VARCHAR(".$islength.")";

     	 	 return "VARCHAR(100)";
     	 }

     	 if(ColumnType::TEXT === $type){
     	 	 if($context->length)
     	 	     return "VARCHAR(".$context->length.")";

     	 	 return match($context->size){
     	 	 	 'regular' => 'TEXT',
     	 	 	 'big'     => 'LONGTEXT',
     	 	 	 'medium'  => 'MEDIUMTEXT',
     	 	 	 'small'   => 'SMALLTEXT',
     	 	 	 'tiny'    => 'TINYTEXT'
     	 	 };
     	 }

     	 if(ColumnType::FLOAT === $type || ColumnType::DOUBLE || ColumnType::DATE || ColumnType::TIME || ColumnType::JSON){
     	 	 return strtoupper($type->value);
     	 }

     	 if(ColumnType::DATETIME === $type){
     	 	 if($context->storage === 'unix')
     	 	 	 return "BIGINT";

     	 	 return strtoupper($type->value);
     	 }

     	 if(ColumnType::DECIMAL === $type){
     	 	 if($context->precision && $context->scale)
     	 	 	 return "DECIMAL(".$context->precision.", ".$context->scale.")";

     	 	 return strtoupper($type->value);
     	 }

     	 if(ColumnType::BOOLEAN === $type){
     	 	 return "TINYINT(1)";
     	 }
     }

     /**
      * Translate a framework field definiton to sql statement
      * */
     public function translate_field_definition(?object $def = null) : string {
     	 if(!$def)
     	 	 return "";

     	 $sql = [$def->column, $this->resolve_db_column_type($def->type, $def)];

     	 if($def->primary){
     	 	 $sql[] = $def->type === ColumnType::CHAR ? "PRIMARY KEY" : "AUTO_INCREMENT PRIMARY KEY";
     	 }

     	 $sql[] = $def->required ? "NOT NULL" : "NULL";

     	 if(ColumnType::DATETIME === $def->type){
     	 	 $sql[] = $def->auto_now_add ? "DEFAULT CURRENT_TIMESTAMP" : "";
		     $sql[] = $def->auto_now ? "ON UPDATE CURRENT_TIMESTAMP" : "";
     	 }

     	 if($def->default){
     	 	 $sql[] = "DEFAULT ".$def->default;
     	 }
		
 	 	 return implode(" ", $sql);
     }

     //Create a database table from migration
     public function create_table_from_migration(string $table, array $column_sqls, array $unique_sqls = [], bool $temporary = false){
     	 $operation = new TableCreateOperation(
	 	 	 table:  $table,
	 	 	 fields: implode(", ", $column_sqls),
	 	 	 temporary: $temporary,
	 	 	 constraints: implode(", ", $unique_sqls)
	 	 );

	 	 $tblcreated = $operation->create($this->connection);

	 	 return $tblcreated;
     }

     //create table from model class
     public function create_table_from_model(string $table, string $model_class, bool $temporary = false){
     	 
     	 $defs = [];
     	 $fields = $model_class::get_fields();
     	 foreach($fields as $f){
     	 	 $defs[] = $this->translate_field_definition($f->get_definition(FieldDefinition::class));
     	 }

     	 $operation = new TableCreateOperation(
	 	 	 table:  $table,
	 	 	 fields: implode(", ", $defs),
	 	 	 temporary: $temporary
	 	 );
	 	 $tblcreated = $operation->create($this->connection);

	 	 return $tblcreated;
     }

     public function supports_window_functions(): bool {
         $version = $this->get_version();

         return $version ? version_compare($version, '8.0', '>=') : false;
     }

     public function supports_returning(): bool {
         return false;
     }

     public function supports_cte(): bool {
     	 $version = $this->get_version();

         return $version ? version_compare($version, '8.0', '>=') : false;
     }

     public function supports_json(): bool {
     	 $version = $this->get_version();

         return $version ? version_compare($version, '8.0', '>=') : false;
     }
 
}


