<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Operations\Crud\RunOperation;
use SaQle\Orm\Connection\Connection;

class RunManager{
	 private string $connection;
	 private string $sql;
	 private string $operation;
	 private ?array $data = null;
	 private bool $multiple = true;

	 public function __construct(string $connection, string $sql, string $operation, ?array $data = null, bool $multiple = true){
	 	 $this->sql = $sql;
	 	 $this->operation = $operation;
	 	 $this->data = $data;
	 	 $this->multiple = $multiple;
	 	 $this->connection = $connection;
	 }

	 public function now(){
	 	 try{
	 	 	 $pdo = resolve(Connection::class, config('connections')[$this->connection]);
	 	 	 $operation = new RunOperation(
		 	 	 sql:       $this->sql,
		 	 	 operation: $this->operation,
		 	 	 data:      $this->data,
		 	 	 multiple:  $this->multiple
		 	 );
		 	 return $operation->run($pdo);
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
