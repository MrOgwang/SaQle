<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\SelectOperationFailedException;
use Exception;
use PDO;

class SelectOperation extends IOperation{

	 public function select(&$pdo){
	 	 try{
	 	 	 $data      = $this->settings['data'];
		     $sql       = $this->settings['sql'];

		     $statement = $pdo->prepare($sql);
		     $response  = $statement->execute($data);

			 if($response === false || $statement->errorCode() !== "00000")
				 throw new SelectOperationFailedException(name: '');

		     return $statement->fetchAll(PDO::FETCH_OBJ);
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

}
