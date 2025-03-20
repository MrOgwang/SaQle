<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\TableCreateOperationFailedException;
use Exception;

class TableDropOperation extends IOperation{
	 public function drop(&$pdo){
	 	 try{
	 	 	 $table     = $this->settings['table'];
		     $temp      = $this->settings['temporary'];
		     $sql       = $temp ? "DROP TEMPORARY TABLE IF EXISTS {$table}" : "DROP TABLE IF EXISTS {$table}";
		     $statement = $pdo->prepare($sql);
			 $response  = $statement->execute($data);

			 if($response === false || $statement->errorCode() !== "00000")
			 	 throw new TableCreateOperationFailedException(name: $table);
		     
			 return true;
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
?>