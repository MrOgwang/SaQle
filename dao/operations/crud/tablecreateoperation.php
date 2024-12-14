<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\TableCreateOperationFailedException;

class TableCreateOperation extends IOperation{

	 public function create(){
	 	 $table    = $this->settings['table'];
		 $fields   = $this->settings['fields'];
		 $temp     = $this->settings['temporary'] ?? false;
		 $sql      = $temp ? "CREATE TEMPORARY TABLE {$table} ({$fields})" : "CREATE TABLE IF NOT EXISTS {$table} ({$fields})";
		 $response = $this->getpdo($this->connection->execute($sql, null, "create"), "create");
		 if($response->error_code !== "00000"){
		 	 throw new TableCreateOperationFailedException(name: $table);
		 	 return false;
		 }
		 return true;
	 }

}
?>