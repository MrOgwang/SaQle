<?php
namespace SaQle\Orm\Database\Manager;

use SaQle\Orm\Database\Manager\Base\DbManager;
use SaQle\Orm\Operations\Crud\TableCreateOperation;
use SaQle\Orm\Connection\Connection;
use SaQle\Orm\Database\ColumnType;

class MySQLDbManager extends DbManager{
	 protected array $tempparams;

	 public function __construct(array $params){
	 	 $this->connection_params = $params;
	 	 $this->tempparams = $params;
	 	 $this->tempparams['database'] = ''; //connect without a database, hence name is empty
	 	 $this->connection = resolve(Connection::class, $this->tempparams);
	 }

	 public function connect(){
	 	 $this->connection = resolve(Connection::class, $this->connection_params);
	 }

	 private function execute($sql, $data = null){
	 	 $statement = $this->connection->prepare($sql);
	     $response  = $statement->execute($data);
	     return ['statement' => $statement, 'response' => $response];
	 }

	 public function check_database_exists($ctx){
         $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
         $data = [$this->connection_params['database']];

         $statement = $this->execute($sql, $data)['statement'];
         
         $object = $statement->fetchObject(); 
         return $object ? true : false;
     }

	 public function create_database(){
		 $char_set = $this->connection_params['char_set'] ?? 'utf8';
		 $collation = $this->connection_params['collation'] ?? $this->charset_and_collations['utf8']['collations'][0];
		 $db_name = $this->connection_params['database'];
		 $sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET $char_set COLLATE $collation";
		 $data = []; //[$db_name, $char_set, $collation];
		 return $this->execute($sql, $data)['response'];
	 }

	 /**
      * Create a new database table.
      * */
     public function create_table($table, $model_class, $temporary = false){
     	 $model = $model_class::make();
     	 $unique_field_names = $model->get_unique_field_names(); 
     	 $defs  = $model->get_field_definitions();

     	 $operation = new TableCreateOperation(
	 	 	 table:  $table,
	 	 	 fields: implode(", ", $defs),
	 	 	 temporary: $temporary
	 	 );
	 	 $tblcreated = $operation->create($this->connection);

	 	 //add unique constraints
	 	 if(!empty($unique_field_names)){
	 	 	 $this->add_unique_columns($table, $unique_field_names, true);
	 	 }

	 	 return $tblcreated;
     }

     public function drop_table($table){
     	 $sql = "DROP TABLE IF EXISTS $table";
		 $data = null;
		 return $this->execute($sql, $data)['response'];
     }

     private function table_column_exists($table, $column){
     	 $sql = "SELECT IF(count(*) = 1, 'Exist','Not Exist') AS result FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?";
         $data = [$this->connection_params['database'], $table, $column];
         $statement = $this->execute($sql, $data)['statement'];
         $object = $statement->fetchObject(); 
         return $object->result == "Exist" ? true : false;
     }

     /**
      * Columns here comes in the form of associative array where column name is the key and column definition is the value.
      * Example: ['user_id' => 'user_id TEXT NOT NULL']
      * */
     public function add_columns($table, $columns){
     	 /**
     	  * Only add columns that are not presently existing on this table.
     	  * */
     	 $columns_to_add = [];
     	 foreach($columns as $col_name => $col_def){
     	 	if(!$this->table_column_exists($table, $col_name)){
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
     public function drop_columns($table, $columns){
     	 /**
     	  * Only drop columns that are presently existing on this table.
     	  * */
     	 $columns_to_drop = [];
     	 foreach($columns as $col_name => $col_def){
     	 	if($this->table_column_exists($table, $col_name)){
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

     public function add_unique_columns($table, $columns, $together){
     	 if($together){
     	     $sql = "ALTER TABLE $table ADD CONSTRAINT unique_".implode('_', $columns)." UNIQUE (".implode(", ", $columns).")";
     	     return $this->execute($sql)['response'];
     	 }else{
     	 	 foreach($columns as $c){
     	 	 	 $sql = "ALTER TABLE $table ADD CONSTRAINT unique_".$c." UNIQUE (".$c.")";
     	 	 	 $ua = $this->execute($sql)['response'];
     	 	 }
     	 	 return true;
     	 }
     }

     public function drop_unique_columns($table, $columns, $together){
     	 if($together){
     	     $sql = "ALTER TABLE $table DROP INDEX unique_".implode('_', $columns);
     	     return $this->execute($sql)['response'];
     	 }else{
     	 	 foreach($columns as $c){
     	 	 	 $sql = "ALTER TABLE $table DROP INDEX unique_".$c;
     	 	 	 $ua = $this->execute($sql)['response'];
     	 	 }
     	 	 return true;
     	 }
     }

     private function get_db_column_type(ColumnType $type, $context){

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

     public function translate_field_definition(?object $def = null) : string {
     	 if(!$def)
     	 	 return "";

     	 $sql = [$def->column, $this->get_db_column_type($def->type, $def)];

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
}


