<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\SelectOperationFailedException;

class SelectOperation extends IOperation{

	 public function select(){
		 $data     = $this->settings['data'];
		 $sql      = $this->settings['sql'];
		 $response = $this->getpdo($this->connection->execute($sql, $data, "select"), "select");
		 if($response->error_code !== "00000"){
		 	 throw new SelectOperationFailedException(name: $name);
		 	 return;
		 }
		 return $response->rows;
	 }

}
?>