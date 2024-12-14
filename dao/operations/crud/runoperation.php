<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;

class RunOperation extends IOperation{

	 public function run(){
	 	 $data      = $this->settings['data'];
		 $sql       = $this->settings['sql'];
		 $operation = $this->settings['operation'];
		 $multiple  = $this->settings['multiple'];

		 $response = $this->getpdo($this->connection->execute($sql, $data, $operation), $operation);
		 if($response->error_code !== "00000"){
		 	 throw new \Exception("Operation failed!");
		 	 return;
		 }

		 if($multiple){
		 	return $response->rows ?? true;
		 }elseif(!$multiple && $response->rows){
		 	return $response->rows[0];
		 }else{
		 	return null;
		 }
	 }

}
?>