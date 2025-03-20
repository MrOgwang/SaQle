<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use Exception;

class RunOperation extends IOperation{

	 public function run(){
	 	 /*try{
	 	 	 $data      = $this->settings['data'];
		     $sql       = $this->settings['sql'];
		     $operation = $this->settings['operation'];
		     $multiple  = $this->settings['multiple'];
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 	 

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
		 }*/
	 }

}
?>