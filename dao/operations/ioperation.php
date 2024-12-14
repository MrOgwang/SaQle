<?php
namespace SaQle\Dao\Operations;

use SaQle\Dao\Connection\Interfaces\IConnection;

abstract class IOperation{
	 protected $settings;
	 public function __construct(protected IConnection $connection){}
	 public function settings($settings){
	 	$this->settings = $settings;
	 }
	 protected function getpdo($response, $operation_type = ""){
		 $statement = $response['statement'];
		 $pdo_statement_object = ["error_code" => $statement->errorCode(), "row_count" => $statement->rowCount()];

         $prmkeytype = $this->settings['prmkeytype'] ?? PRIMARY_KEY_TYPE;
		 if($operation_type === "select"){
		 	 $pdo_statement_object['rows'] = $statement->fetchAll(\PDO::FETCH_OBJ);
		 }elseif($operation_type === "insert" && $prmkeytype === "AUTO"){
		 	 $pdo_statement_object['last_insert_id'] = $response['last_insert_id'];
		 }

		 return (Object)$pdo_statement_object;
	 }
}
?>