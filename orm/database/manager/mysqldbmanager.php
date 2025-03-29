<?php
namespace SaQle\Orm\Database\Manager;

use SaQle\Orm\Database\Manager\Base\DbManager;
use SaQle\Orm\Operations\Crud\TableCreateOperation;
use SaQle\Orm\Connection\Connection;

class MySQLDbManager extends DbManager{
	 public function __construct(...$params){
	 	 $this->connection_params = $params;
	 	 $real_params = DB_CONTEXT_CLASSES[$this->connection_params['ctx']];
	 	 $real_params['name'] = ''; //connect without a database, hence name is empty
	 	 $this->connection = resolve(Connection::class, $real_params);
	 }

	 public function connect(){
	 	 $this->connection = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->connection_params['ctx']]);
	 }

	 private function execute($sql, $data = null){
	 	 $statement = $this->connection->prepare($sql);
	     $response  = $statement->execute($data);
	     return ['statement' => $statement, 'response' => $response];
	 }

	 public function check_database_exists($ctx){
         $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
         $data = [$this->connection_params['name']];

         $statement = $this->execute($sql, $data)['statement'];
         
         $object = $statement->fetchObject(); 
         return $object ? true : false;
     }

	 public function create_database(){
		 $char_set = $this->connection_params['char_set'] ?? 'utf8';
		 $collation = $this->connection_params['collation'] ?? $this->charset_and_collations['utf8']['collations'][0];
		 $db_name = $this->connection_params['name'];
		 $sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET $char_set COLLATE $collation";
		 $data = []; //[$db_name, $char_set, $collation];
		 return $this->execute($sql, $data)['response'];
	 }

	 /**
      * Create a new database table.
      * */
     public function create_table($table, $model_class, $temporary = false){
     	 $model = $model_class::state();
     	 $unique_fields = $model->meta->unique_fields; 
     	 $unique_together = $model->meta->unique_together;
     	 $defs  = $model->get_field_definitions();

     	 $operation = new TableCreateOperation(
	 	 	 table:  $table,
	 	 	 fields: implode(", ", $defs),
	 	 	 temporary: $temporary
	 	 );
	 	 $tblcreated = $operation->create($this->connection);

	 	 //add unique constraints
	 	 if(!empty($unique_fields)){
	 	 	 $this->add_unique_columns($table, $unique_fields, $unique_together);
	 	 }

	 	 return $tblcreated;
     }

     public function drop_table($table, $temporary = false){
     	 $sql = "DROP TABLE IF EXISTS $table";
		 $data = null;
		 return $this->execute($sql, $data)['response'];
     }

     private function table_column_exists($table, $column){
     	 $sql = "SELECT IF(count(*) = 1, 'Exist','Not Exist') AS result FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?";
         $data = [$this->connection_params['name'], $table, $column];
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
     
}
?>

