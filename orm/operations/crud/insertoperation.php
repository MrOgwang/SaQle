<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Core\Exceptions\Model\InsertOperationFailedException;
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
			 $error_code = $statement->errorCode();

			 if($response === false || $error_code !== "00000"){
			 	 throw new InsertOperationFailedException([
			 	 	'table' => $table,
			 	 	'statement_error_code' => $error_code
			 	 ]);
			 }

			 return (Object)['last_insert_id' => $pdo->lastInsertId(), 'row_count' => $statement->rowCount()];
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
