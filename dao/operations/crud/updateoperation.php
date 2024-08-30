<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\{UpdateOperationFailedException, SelectOperationFailedException};

class UpdateOperation extends IOperation{

	 public function update(){ 
	 	 try{
	 	 	 $data     = $this->settings['where_clause']->data ? array_merge($this->settings['values'], $this->settings['where_clause']->data) 
		 	             : $this->settings['values'];
			 $database = $this->settings['database_name'];
			 $table    = $this->settings['table_name'];
			 $clause   = $this->settings['where_clause']->clause;
			 $fieldstring = implode(" = ?, ", $this->settings['fields'])." = ?";
			 $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";
			 $response = $this->getpdo($this->connection->execute($sql, $data, "update"), "update");
			 if($response->error_code !== "00000"){
			 	 throw new UpdateOperationFailedException(name: $table);
			 	 return false;
			 }

			 return $response;
	 	 }catch(\Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
?>