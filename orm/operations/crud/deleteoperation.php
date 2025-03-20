<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\DeleteOperationFailedException;
use Exception;

class DeleteOperation extends IOperation{
	 public function delete(&$pdo){
	 	 try{
	 	 	 $data      = $this->settings['where_clause']->data;
		     $database  = $this->settings['database_name'];
		     $table     = $this->settings['table_name'];
		     $clause    = $this->settings['where_clause']->clause;
		     $sql       = "DELETE FROM {$database}.{$table}{$clause}";
		     $statement = $pdo->prepare($sql);
			 $response  = $statement->execute($data);

			 if($response === false || $statement->errorCode() !== "00000")
			 	 throw new DeleteOperationFailedException(name: $table);

			 return $statement->rowCount() > 0 ? true : false;
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
?>