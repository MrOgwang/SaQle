<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\InsertOperationFailedException;

class InsertOperation extends IOperation{

	 public function insert(){
	 	 $fields       = $this->settings['fields'];
		 $data         = $this->settings['data'];
		 $prmkeytype   = $this->settings['prmkeytype'];
		 $prmkeyname   = $this->settings['prmkeyname'];
		 $prmkeyvalues = $this->settings['prmkeyvalues'];
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
		 	 return false;
		 }

		 if($prmkeytype && $prmkeyname && $prmkeyvalues){
		 	 $sql2 = "SELECT * FROM {$database}.{$table} WHERE {$prmkeyname} IN (".str_repeat('?, ', count($prmkeyvalues) - 1). '?'.")";
		 	 $response = $this->getpdo($this->connection->execute($sql2, $prmkeyvalues, "select", $prmkeytype), "select");
			 if($response->error_code !== "00000"){
			 	 throw new InsertOperationFailedException(name: $table);
			 	 return false;
			 }
			 return $response->rows;
		 }

		
		 return $response->rows_count > 0 ? true : false;
	 }

}
?>