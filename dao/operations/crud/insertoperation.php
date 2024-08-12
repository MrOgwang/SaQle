<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\InsertOperationFailedException;

class InsertOperation extends IOperation{

	 public function insert(){
	 	 try{
	 	 	 $fields       = $this->settings['fields'];
			 $data         = $this->settings['data'];
			 $prmkeytype   = $this->settings['prmkeytype'];

			 $values       = [];
			 $row_count    = count($data);
			 foreach($data as $row){
			 	 $values[] = array_values($row);
			 }
			 $database = $this->settings['database'];
			 $table    = $this->settings['table'];
			 $fieldstring = implode(", ", $fields);
			 $valstring   = str_repeat('?, ', count($fields) - 1). '?';
			 $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1). "($valstring)";
			 $response = $this->getpdo($this->connection->execute($sql, array_merge(...$values), "insert", $prmkeytype), "insert");
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