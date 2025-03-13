<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\SelectOperationFailedException;

class TotalOperation extends IOperation{

	 public function total(){
		 $data     = $this->settings['where_clause']->data;
		 $database = $this->settings['database_name'];
		 $table    = $this->settings['table_name'];
		 $sql      = "SELECT COUNT(*) AS records_count FROM {$database}.{$table}";
		 if(isset($this->settings['table_aliase']) && $this->settings['table_aliase']){
		 	 $sql .= " AS ".$this->settings['table_aliase'];
		 }
		 $sql .= $this->settings['join_clause'];
		 $sql .= $this->settings['where_clause']->clause;
		 $sql .= $this->settings['order_clause'];
		 $sql .= $this->settings['limit_clause'];

		 $response = $this->getpdo($this->connection->execute($sql, $data, "select"), "select");
		 if($response->error_code !== "00000"){
		 	 throw new SelectOperationFailedException(name: $name);
		 	 return;
		 }
		 
		 return $response->rows[0]->records_count;
	 }

}
?>