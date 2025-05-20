<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\UpdateOperationFailedException;

class UpdateOperation extends IOperation{

	 public function update(&$pdo){ 
	 	 try{
			 $sql       = $this->settings['sql'];
			 $data      = $this->settings['data'];
			 $table     = $this->settings['table'];
			 $statement = $pdo->prepare($sql);
			 $response  = $statement->execute($data);

			 if($response === false || $statement->errorCode() !== "00000")
			 	 throw new UpdateOperationFailedException(name: $table);

			 return (Object)['row_count' => $statement->rowCount()];
	 	 }catch(\Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
