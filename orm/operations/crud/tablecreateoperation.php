<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\TableCreateOperationFailedException;
use Exception;

class TableCreateOperation extends IOperation{

	 public function create(&$pdo){
	 	 try{
	 	 	 $table     = $this->settings['table'];
		     $fields    = $this->settings['fields'];
		     $temp      = $this->settings['temporary'] ?? false;
		     $sql       = $temp ? "CREATE TEMPORARY TABLE IF NOT EXISTS {$table} ({$fields})" : "CREATE TABLE IF NOT EXISTS {$table} ({$fields})";
		     $statement = $pdo->prepare($sql);
			 $response  = $statement->execute(null);

			 if($response === false || $statement->errorCode() !== "00000")
			 	 throw new TableCreateOperationFailedException(name: $table);

		     return true;
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

}
?>