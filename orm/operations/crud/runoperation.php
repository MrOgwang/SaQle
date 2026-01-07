<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Core\Exceptions\Model\RunOperationFailedException;
use Exception;
use PDO;

class RunOperation extends IOperation{

     private function handle_insert($statement, $pdo){
     	 return (Object)['last_insert_id' => $pdo->lastInsertId(), 'row_count' => $statement->rowCount()];
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

	 public function run(&$pdo){
	 	 try{
	 	 	 $data      = $this->settings['data'];
		     $sql       = $this->settings['sql'];
		     $operation = $this->settings['operation'];
		     $multiple  = $this->settings['multiple'];

		     $statement  = $pdo->prepare($sql);
			 $response   = $statement->execute($data);
			 $error_code = $statement->errorCode();

			 if($response === false || $error_code !== "00000"){
			 	 throw new RunOperationFailedException([
			 	 	 'data'      => $data,
			 	 	 'sql'       => $sql,
			 	 	 'operation' => $operation,
			 	 	 'multiple'  => $multiple
			 	 ]);
			 }

			 return match($operation){
			 	 'insert' => $this->handle_insert($statement, $pdo),
			 	 'select' => $this->handle_select($statement, $multiple),
			 	 'update' => $this->handle_update($statement),
			 	 'delete' => $this->handle_delete($statement)
			 };
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

}
