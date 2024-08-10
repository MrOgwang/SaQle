<?php
namespace SaQle\Dao\Operations\Crud;

use SaQle\Dao\Operations\IOperation;
use SaQle\Dao\Operations\Crud\Exceptions\SelectOperationFailedException;

class SelectOperation extends IOperation{

	 public function select(){
	 	 $tomodel       = $this->settings['tomodel'];
	 	 $daoclass    = $this->settings['daoclass'];
		 $data        = $this->settings['where_clause']->data;
		 $select      = $this->settings['select_clause'];
		 $database    = $this->settings['database_name'];
		 $table       = $this->settings['table_name'];
		 $sql = "SELECT {$select} FROM {$database}.{$table}";
		 if(isset($this->settings['table_aliase']) && $this->settings['table_aliase']){
		 	 $sql .= " AS ".$this->settings['table_aliase'];
		 }
		 $sql .= $this->settings['join_clause'];
		 $sql .= $this->settings['where_clause']->clause;
		 $sql .= $this->settings['order_clause'];
		 $sql .= $this->settings['limit_clause'];

		 $response = $this->getpdo($this->connection->execute($sql, $data, "select"), "select", $tomodel, $daoclass);
		 if($response->error_code !== "00000"){
		 	 throw new SelectOperationFailedException(name: $name);
		 	 return;
		 }
		 return $response->rows;
	 }

}
?>