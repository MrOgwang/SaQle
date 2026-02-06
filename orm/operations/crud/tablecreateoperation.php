<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Core\Exceptions\Model\TableCreateOperationFailedException;
use Exception;

class TableCreateOperation extends IOperation{

	 public function create(&$pdo){
	 	 try{
	 	 	 $table       = $this->settings['table'];
		     $fields      = $this->settings['fields'];
		     $constraints = $this->settings['constraints'] ?? null;
		     $temp        = $this->settings['temporary'] ?? false;
		     $sql         = $temp ? "CREATE TEMPORARY TABLE IF NOT EXISTS {$table} ({$fields})" : "CREATE TABLE IF NOT EXISTS {$table} ({$fields})";
		     if($constraints){
		     	 $constraints = ", ".$constraints;
		     	 $sql = $temp ? 
		     	 "CREATE TEMPORARY TABLE IF NOT EXISTS {$table} ({$fields}{$constraints})" : 
		     	 "CREATE TABLE IF NOT EXISTS {$table} ({$fields}{$constraints})";
		     }
		     $statement   = $pdo->prepare($sql);
			 $response    = $statement->execute(null);
			 $error_code  = $statement->errorCode();

			 if($response === false || $error_code !== "00000"){
			 	 throw new TableCreateOperationFailedException([
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
