<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\UpdateOperationFailedException;

class UpdateOperation extends IOperation{

	 public function update(&$pdo){ 
	 	 try{
	 	 	 $data     = $this->settings['where_clause']->data ? array_merge($this->settings['values'], $this->settings['where_clause']->data) 
		 	             : $this->settings['values'];
			 $database = $this->settings['database_name'];
			 $table    = $this->settings['table_name'];
			 $clause   = $this->settings['where_clause']->clause;
			 $fieldstring = implode(" = ?, ", $this->settings['fields'])." = ?";
			 $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";
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
?>