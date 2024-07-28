<?php
namespace SaQle\Dao\DbContext\Manager;

use SaQle\Dao\DbContext\Manager\Base\DbManager;
use SaQle\Services\Container\Cf;
use SaQle\Services\Container\ContainerService;
use SaQle\Dao\Commands\Crud\{TableCreateCommand};
use SaQle\Dao\Operations\Crud\{TableCreateOperation};

class MySQLDbManager extends DbManager{
	 public function __construct(...$params){
	 	 $this->connection_params = $params;
	 	 $paramscopy = array_combine(array_keys($params), array_values($params));
         $paramscopy['name'] = "";
	 	 $this->vconnection = (Cf::create(ContainerService::class))->createConnection(...
             ['context' => (Cf::create(ContainerService::class))->createDbContextOptions(...$paramscopy)
         ]);
         $this->connection = (Cf::create(ContainerService::class))->createConnection(...
             ['context' => (Cf::create(ContainerService::class))->createDbContextOptions(...$params)
         ]);
	 }

	 public function check_database_exists($ctx){
         $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
         $data = [$this->connection_params['name']];
         $statement = $this->vconnection->execute($sql, $data)['statement'];
         $object = $statement->fetchObject(); 
         return $object ? true : false;
     }

	 public function create_database(){
		 $char_set = $this->connection_params['char_set'] ?? 'utf8';
		 $collation = $this->connection_params['collation'] ?? $this->charset_and_collations['utf8']['collations'][0];
		 $db_name = $this->connection_params['name'];
		 $sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET $char_set COLLATE $collation";
		 $data = []; //[$db_name, $char_set, $collation];
		 return $this->vconnection->execute($sql, $data)['response'];
	 }

	 /**
      * Create a new database table.
      * */
     public function create_table($table, $model_class){
     	 $model = new $model_class();
     	 $defs  = $model->get_field_definitions();
     	 /*add CreatorModifierFields, CreateModifyDateTimeFields and SoftDeleteFields if these attributes exist on data access object*/
     	 if($model->get_auto_cm()){
     	 	 $auto_cm_fields = $model->get_auto_cm_fields();
     	 	 $defs[] = $auto_cm_fields[0]." VARCHAR(100) NOT NULL";
     	 	 $defs[] = $auto_cm_fields[1]." VARCHAR(100) NOT NULL";
     	 }
     	 if($model->get_auto_cmdt()){
     	 	 $auto_cmdt_fields = $model->get_auto_cmdt_fields();
     	 	 $defs[] = $auto_cmdt_fields[0]." BIGINT(20) NOT NULL";
     	 	 $defs[] = $auto_cmdt_fields[1]." BIGINT(20) NOT NULL";
     	 }
     	 if($model->get_soft_delete()){
     	 	 $soft_delete_fields = $model->get_soft_delete_fields();
     	 	 $defs[] = $soft_delete_fields[0]." TINYINT(1) NOT NULL";
     	 }

     	 /*setup a create command*/
	 	 $this->crud_command = new TableCreateCommand(
	 	 	 new TableCreateOperation($this->connection),
	 	 	 table:  $table,
	 	 	 fields: implode(", ", $defs)
	 	 );

	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
     }

     public function drop_table($table){
     	 $sql = "DROP TABLE IF EXISTS $table";
		 $data = null;
		 return $this->connection->execute($sql, $data)['response'];
     }

     private function table_column_exists($table, $column){
     	 $sql = "SELECT IF(count(*) = 1, 'Exist','Not Exist') AS result FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?";
         $data = [$this->connection_params['name'], $table, $column];
         $statement = $this->connection->execute($sql, $data)['statement'];
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
		 return $this->connection->execute($sql, $data)['response'];
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
		 return $this->connection->execute($sql, $data)['response'];
     }
     
}
?>