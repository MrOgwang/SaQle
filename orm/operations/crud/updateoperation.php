<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Core\Exceptions\Model\UpdateOperationFailedException;

class UpdateOperation extends IOperation {

	 public function update(&$pdo){ 
	 	 try{
			 $sql       = $this->settings['sql'];
			 $data      = $this->settings['data'];
			 $table     = $this->settings['table'];
			 $statement = $pdo->prepare($sql);
			 $response  = $statement->execute($data);
			 $error_code = $statement->errorCode();

			 if($response === false || $error_code !== "00000"){
			 	 throw new UpdateOperationFailedException([
			 	 	 'table' => $table,
			 	 	 'statement_error_code' => $error_code
			 	 ]);
			 }

			 return (Object)['row_count' => $statement->rowCount()];
	 	 }catch(\Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
