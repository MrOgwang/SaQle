<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\DeleteOperationFailedException;

class DeleteOperation extends IOperation{

	 public function delete(){

	 	 $data     = $this->settings['where_clause']->data;
		 $database = $this->settings['database_name'];
		 $table    = $this->settings['table_name'];
		 $clause   = $this->settings['where_clause']->clause;
		 $sql      = "DELETE FROM {$database}.{$table}{$clause}";

		 $response = $this->getpdo($this->connection->execute($sql, $data, "delete"), "delete");
		 if($response->error_code !== "00000"){
		 	 throw new DeleteOperationFailedException(name: $table);
		 	 return false;
		 }
		 
		 return $response->row_count > 0 ? true : false;
	 }

}
?>