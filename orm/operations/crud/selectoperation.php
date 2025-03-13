<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\SelectOperationFailedException;

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