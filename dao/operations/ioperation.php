<?php
namespace SaQle\Dao\Operations;

use SaQle\Dao\Connection\Interfaces\IConnection;

abstract class IOperation{
	 protected $settings;
	 public function __construct(protected IConnection $connection){}
	 public function settings($settings){
	 	$this->settings = $settings;
	 }
	 protected function getpdo($response, $operation_type = "", $todao = false, $daoclass = ""){
		 $statement = $response['statement'];
		 $pdo_statement_object = array("error_code" => $statement->errorCode(), "row_count" => $statement->rowCount(), "rows" => []);
		 if($operation_type == "select"){
		 	 $daoclass = $todao ? $daoclass : "stdClass";
		     while($row = $statement->fetchObject($daoclass)){
				 array_push($pdo_statement_object['rows'], $row);
			 }
		 }else if($operation_type == "insert" && $this->settings['prmkeytype'] === "AUTO"){
			 $pdo_statement_object['last_insert_id'] = $response['last_insert_id'];
		 }
		 return (Object)$pdo_statement_object;
	 }
}
?>