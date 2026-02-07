<?php
namespace SaQle\Orm\Database\Drivers;

use SaQle\Orm\Database\Config\ConnectionConfig;
use SaQle\Orm\Connection\ConnectionManager;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;
use SaQle\Orm\Entities\Model\Manager\QueryManager;
use SaQle\Core\Exceptions\Model\TableDropOperationFailedException;
use SaQle\Core\Exceptions\Model\TableCreateOperationFailedException;

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

	 public function drop_table(string $table, bool $temporary = false){
         
         $sql = $temporary ? "DROP TEMPORARY TABLE IF EXISTS {$table}" : "DROP TABLE IF EXISTS {$table}";
         [$statement, $response] = array_values($this->execute($sql));
         $error_code = $statement->errorCode();

         if($response === false || $error_code !== "00000"){
             throw new TableDropOperationFailedException([
                 'table' => $table,
                 'statement_error_code' => $error_code
             ]);
         }

         return true;
     }

     public function set_truncate_query(QueryManager $manager) : void {
         $database = $this->config->get_database();
         $sql = "TRUNCATE TABLE {$database}.{$table}";

         $manager->set_sql($sql);
     }

     public function set_temporary_delete_query(QueryManager $manager) : void {
         $where_clause = $manager->wbuilder->get_where_clause(
             $manager->get_query_reference_map(), 
             $manager->get_configurations()
         );
         $data = $where_clause->data ? array_merge([1], $where_clause->data) : [1];
         $database = $this->config->get_database();
         $table = $manager->table_name();
         $fields = ['deleted'];
         $clause   = $where_clause->clause;
         $fieldstring = implode(" = ?, ", $fields)." = ?";
         $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";

         $manager->set_sql($sql);
         $manager->set_data($data);
     }

     public function set_permanent_delete_query(QueryManager $manager) : void {
         $where_clause = $manager->wbuilder->get_where_clause(
             $manager->get_query_reference_map(), 
             $manager->get_configurations()
         );
         $data = $where_clause->data ?? null;
         $database = $this->config->get_database();
         $table = $manager->table_name();
         $clause = $where_clause->clause;
         $sql = "DELETE FROM {$database}.{$table}{$clause}";

         $manager->set_sql($sql);
         $manager->set_data($data);
     }

     public function set_update_query(QueryManager $manager) : void {
         $where_clause = $manager->wbuilder->get_where_clause(
             $manager->get_query_reference_map(), 
             $manager->get_configurations()
         );
         $clean_data = $manager->get_clean_data();
         $data = $where_clause->data ? array_merge(array_values($clean_data), $where_clause->data) : array_values($clean_data);
         $database = $this->config->get_database();
         $table = $manager->table_name();
         $fields = array_keys($clean_data);
         $clause   = $where_clause->clause;
         $fieldstring = implode(" = ?, ", $fields)." = ?";
         $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";

         $manager->set_sql($sql);
         $manager->set_data($data);
     }

     public function set_insert_query(QueryManager $manager) : void {
         $fields        = array_keys(array_values($manager->get_container()->data)[0]);
         $data          = array_values($manager->get_container()->data);
         $values        = [];
         $row_count     = count($data);
         foreach($data as $row){
             $values[]  = array_values($row);
         }
         $database      = $this->config->get_database();
         $table         = $manager->table_name();
         $fieldstring   = implode(", ", $fields);
         $valstring     = str_repeat('?, ', count($fields) - 1). '?';
         $prepared_data = array_merge(...$values);
         if($manager->get_model()->meta->action_on_duplicate === 'ABORT_WITH_ERROR'){
             $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1). "($valstring)";
         }elseif($manager->get_model()->meta->action_on_duplicate === 'INSERT_MINUS_DUPLICATE'){
             $sql = "INSERT IGNORE INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1)."($valstring)";
         }elseif($manager->get_model()->meta->action_on_duplicate === 'UPDATE_ON_DUPLICATE'){
             $exclude = array_merge($manager->get_model()::get_unique_field_names(), [$manager->get_model()::get_pk_name()]);
             $toupdate = array_map(function($f){
                 return "$f = VALUES($f)";
             }, array_diff($fields, $exclude));
             $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1)."($valstring) ON DUPLICATE KEY UPDATE ".implode(', ', $toupdate);
         }elseif($manager->get_model()->meta->action_on_duplicate === 'RETURN_EXISTING'){
             $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1)."($valstring) ON DUPLICATE KEY UPDATE {$manager->get_model()->meta->pk_name} = {$manager->get_model()->meta->pk_name}";
         }

         $manager->set_sql($sql);
         $manager->set_data($prepared_data);
     }

     public function set_read_query(QueryManager $manager) : void {
         $where_clause = $manager->wbuilder->get_where_clause(
             $manager->get_query_reference_map(), 
             $manager->get_configurations()
         );
         $join_clause = $manager->jbuilder->construct_join_clause($manager->get_query_reference_map());
         $data = null;
         if($where_clause->data || $join_clause->data){
             $join_clause_data = $join_clause->data ?? [];
             $where_clause_data = $where_clause->data ?? [];
             $data = array_merge($join_clause_data, $where_clause_data);
         }

         $select       = $manager->get_selected();
         $database     = $manager->get_query_reference_map()->find_database_name(0);
         $table        = $manager->get_query_reference_map()->find_table_name(0);
         $table_aka    = $manager->get_query_reference_map()->find_table_aliase(0);
         $table_ref    = $manager->get_query_reference_map()->find_table_refernce(0);
         
         $sql          = "SELECT {$select} FROM ";
         $from_ref     = "";
         if($manager->get_configurations()['ftnm'] === 'N-WITH-A'){ //use name and aliase
             $from_ref = $table_ref ?? ($manager->get_configurations()['ftqm'] === 'F-QUALIFY' ? $database.".".$table : $table);
             if($table_aka){
                 $from_ref .= " AS ".$table_aka;
             }
         }elseif($manager->get_configurations()['ftnm'] === 'N-ONLY'){ //use only the table name
             $from_ref = $table_ref ?? ($manager->get_configurations()['ftqm'] === 'F-QUALIFY' ? $database.".".$table : $table);
         }elseif($manager->get_configurations()['ftnm'] === 'A-ONLY'){ //use only the aliase name
             $from_ref = $table_ref ?? ($manager->get_configurations()['ftqm'] === 'F-QUALIFY' ? $database.".".$table : $table);
             $from_ref = $table_aka ? $table_aka : $from_ref;
         }

         $sql         .= $from_ref;
         $sql         .= $join_clause->clause;
         $sql         .= $where_clause->clause;
         $sql         .= $manager->get_groupby_clause();
         $sql         .= $manager->obuilder->construct_order_clause();
         $sql         .= $manager->lbuilder->construct_limit_clause();
         
         $manager->set_sql($sql);
         $manager->set_data($data);
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

     private function create_table(string $table, string $fields, bool $temporary = false, string $constraints = ""){
         $sql = $temporary ? "CREATE TEMPORARY TABLE IF NOT EXISTS {$table} ({$fields})" : "CREATE TABLE IF NOT EXISTS {$table} ({$fields})";
         if($constraints){
             $constraints = ", ".$constraints;
             $sql = $temporary ? 
             "CREATE TEMPORARY TABLE IF NOT EXISTS {$table} ({$fields}{$constraints})" : 
             "CREATE TABLE IF NOT EXISTS {$table} ({$fields}{$constraints})";
         }

         [$statement, $response] = array_values($this->execute($sql));
         $error_code = $statement->errorCode();

         if($response === false || $error_code !== "00000"){
             echo "SQL: $sql\n";
             throw new TableCreateOperationFailedException([
                 'table' => $table,
                 'statement_error_code' => $error_code
             ]);
         }

         return true;
     }

     //Create a database table from migration
     public function create_table_from_migration(string $table, array $column_sqls, array $unique_sqls = [], bool $temporary = false){
         return $this->create_table(
             $table,
             implode(", ", $column_sqls),
             $temporary, 
             implode(", ", $unique_sqls)
         );
     }

     //create table from model class
     public function create_table_from_model(string $table, string $model_class, bool $temporary = false){
     	 
     	 $defs = [];
     	 $fields = $model_class::get_fields();
     	 foreach($fields as $f){
     	 	 $defs[] = $this->translate_field_definition($f->get_definition(FieldDefinition::class));
     	 }

         return $this->create_table(
             $table,
             implode(", ", $defs),
             $temporary
         );
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


