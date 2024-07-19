<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\{UpdateOperationFailedException, SelectOperationFailedException};

class UpdateOperation extends IOperation{

	 public function update(){ 

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

		 $sql2 = "SELECT * FROM {$database}.{$table}{$clause}";
	 	 $response = $this->getpdo($this->connection->execute($sql2, $this->settings['where_clause']->data, "select"), "select");
		 if($response->error_code !== "00000"){
		 	 throw new SelectOperationFailedException(name: $table);
		 	 return false;
		 }
		 return $response->rows;
	 }

}
?>