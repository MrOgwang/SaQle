<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Core\Exceptions\Model\TableDropOperationFailedException;
use Exception;

class TableDropOperation extends IOperation{
	 public function drop(&$pdo){
	 	 try{
	 	 	 $table     = $this->settings['table'];
		     $temp      = $this->settings['temporary'];
		     $sql       = $temp ? "DROP TEMPORARY TABLE IF EXISTS {$table}" : "DROP TABLE IF EXISTS {$table}";
		     $statement = $pdo->prepare($sql);
			 $response  = $statement->execute($data);
			 $error_code = $statement->errorCode();

             if($response === false || $error_code !== "00000"){
			 	 throw new TableDropOperationFailedException([
			 	 	 'table' => $table,
			 	 	 'statement_error_code' => $error_code
			 	 ]);
			 }
		     
			 return true;
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
