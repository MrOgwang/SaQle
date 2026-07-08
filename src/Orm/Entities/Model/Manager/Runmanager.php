<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Exceptions\Model\RunOperationFailedException;
use PDO;

class RunManager extends QueryManager {
	
	 private string $operation;
	 private bool $multiple = true;

	 public function __construct(Model $model, string $sql, string $operation, ?array $data = null, bool $multiple = true){
	 	 parent::__construct($model);
	 	 $this->set_sql($sql);
	 	 $this->set_data($data);

	 	 $this->operation = $operation;
	 	 $this->multiple = $multiple;
	 }

	 private function handle_insert($statement){
     	 return (Object)[
     	 	 'last_insert_id' => $this->dbdriver->get_connection()->lastInsertId(), 
     	 	 'row_count' => $statement->rowCount()
     	 ];
     }

     private function handle_select($statement, $multiple){
     	 $rows = $statement->fetchAll(PDO::FETCH_OBJ);
         return $multiple ? $rows : ($rows[0] ?? null);
     }

     private function handle_update($statement){
     	 return (Object)['row_count' => $statement->rowCount()];
     }

     private function handle_delete($statement){
     	 return $statement->rowCount() > 0 ? true : false;
     }

	 public function now(){
	 	 try{
	 	 	 //connect to the database
	 	 	 $this->dbdriver->connect_with_database();

	 	 	 //get query info
	 	 	 $query_info = $this->get_query_info();

	 	 	 //execute
             [$statement, $response] = array_values($this->dbdriver->execute($query_info['sql'], $query_info['data']));
             $error_code = $statement->errorCode();

             if($response === false || $error_code !== "00000"){
			 	 throw new RunOperationFailedException([
			 	 	 'data'      => $query_info['data'],
			 	 	 'sql'       => $query_info['sql'],
			 	 	 'operation' => $this->operation,
			 	 	 'multiple'  => $this->multiple
			 	 ]);
			 }

			 return match($this->operation){
			 	 'insert' => $this->handle_insert($statement),
			 	 'select' => $this->handle_select($statement, $this->multiple),
			 	 'update' => $this->handle_update($statement),
			 	 'delete' => $this->handle_delete($statement)
			 };
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
