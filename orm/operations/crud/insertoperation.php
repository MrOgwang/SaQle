<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\InsertOperationFailedException;

class InsertOperation extends IOperation{

	 public function insert(){
	 	 try{
	 	 	 $sql        = $this->settings['sql'];
			 $data       = $this->settings['data'];
			 $prmkeytype = $this->settings['prmkeytype'];
			 $table      = $this->settings['table'];
			 $response = $this->getpdo($this->connection->execute($sql, $data, "insert", $prmkeytype), "insert");
			 if($response->error_code !== "00000"){
			 	 throw new InsertOperationFailedException(name: $table);
			 }

			 return $response;
	 	 }catch(\Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
?>