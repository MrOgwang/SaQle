<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use Exception;

class RunOperation extends IOperation{

	 public function run(){
	 	 try{
	 	 	 $data      = $this->settings['data'];
		     $sql       = $this->settings['sql'];
		     $operation = $this->settings['operation'];
		     $multiple  = $this->settings['multiple'];

		     $statement  = $pdo->prepare($sql);
			 $response   = $statement->execute($data);

			 if($response === false || $statement->errorCode() !== "00000")
			 	 throw new InsertOperationFailedException(name: $table);

             if($operation === 'insert')
             	 return (Object)['last_insert_id' => $pdo->lastInsertId(), 'row_count' => $statement->rowCount()];

             if($operation === 'select'){
             	 $rows = $statement->fetchAll(PDO::FETCH_OBJ);
             	 return $multiple ? $rows : ($rows[0] ?? null);
             }

             if($operation === 'update')
             	 return (Object)['row_count' => $statement->rowCount()];

             if($operation === 'delete')
             	 return $statement->rowCount() > 0 ? true : false;

	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

}
