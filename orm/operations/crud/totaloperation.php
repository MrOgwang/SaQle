<?php
namespace SaQle\Orm\Operations\Crud;

use SaQle\Orm\Operations\IOperation;
use SaQle\Orm\Operations\Crud\Exceptions\SelectOperationFailedException;
use Exception;

class TotalOperation extends IOperation{

	 public function total(&$pdo){
	 	 try{
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

			 if($response === false || $statement->errorCode() !== "00000")
			 	 throw new SelectOperationFailedException(name: $table);

             $rows = $statement->fetchAll(PDO::FETCH_OBJ);
		     return $rows[0]->records_count;
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
?>