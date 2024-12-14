<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\TableCreateOperationFailedException;

class TableDropOperation extends IOperation{

	 public function drop(){

	 	 $table    = $this->settings['table'];
		 $temp     = $this->settings['temporary'];
		 $sql      = $temp ? "DROP TEMPORARY TABLE IF EXISTS {$table}" : "DROP TABLE IF EXISTS {$table}";
		 $response = $this->getpdo($this->connection->execute($sql, null, "drop"), "drop");
		 if($response->error_code !== "00000"){
		 	 throw new TableCreateOperationFailedException(name: $table);
		 	 return false;
		 }
		 return true;
	 }

}
?>