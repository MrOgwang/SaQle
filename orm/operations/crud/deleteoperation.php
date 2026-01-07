<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Core\Exceptions\Model\DeleteOperationFailedException;
use Exception;

class DeleteOperation extends IOperation{
	 public function delete(&$pdo){
	 	 try{
	 	 	 $sql       = $this->settings['sql'];
			 $data      = $this->settings['data'];
			 $table     = $this->settings['table'];
			 $statement = $pdo->prepare($sql);
			 $response  = $statement->execute($data);
			 $error_code = $statement->errorCode();
			 
			 if($response === false || $error_code !== "00000"){
			 	 throw new DeleteOperationFailedException([
			 	 	 'table' => $table, 
			 	 	 'statement_error_code' => $error_code
			 	 ]);
			 }

			 return $statement->rowCount() > 0 ? true : false;
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
