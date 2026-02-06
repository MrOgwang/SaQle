<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Core\Exceptions\Model\SelectOperationFailedException;
use Exception;
use PDO;

class SelectOperation extends IOperation{

	 public function select(&$pdo){
	 	 try{
	 	 	 $data      = $this->settings['data'];
		     $sql       = $this->settings['sql'];
		     
		     $statement = $pdo->prepare($sql);
		     $response  = $statement->execute($data);
		     $error_code = $statement->errorCode();

			 if($response === false || $error_code !== "00000"){
			 	 throw new SelectOperationFailedException([
			 	 	'table' => '',
			 	 	'statement_error_code' => $error_code
			 	 ]);
			 }

		     return $statement->fetchAll(PDO::FETCH_OBJ);
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

}
