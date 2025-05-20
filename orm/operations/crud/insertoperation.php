<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\InsertOperationFailedException;
use Exception;

class InsertOperation extends IOperation{

	 public function insert(&$pdo){
	 	 try{
	 	 	 $sql        = $this->settings['sql'];
			 $data       = $this->settings['data'];
			 $prmkeytype = $this->settings['prmkeytype'];
			 $table      = $this->settings['table'];
			 $statement  = $pdo->prepare($sql);
			 $response   = $statement->execute($data);

			 if($response === false || $statement->errorCode() !== "00000")
			 	 throw new InsertOperationFailedException(name: $table);

			 return (Object)['last_insert_id' => $pdo->lastInsertId(), 'row_count' => $statement->rowCount()];
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
