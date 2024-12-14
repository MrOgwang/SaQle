<?php
namespace SaQle\Dao\Limit\Manager;

use SaQle\Dao\Limit\Interfaces\{ILimit, ILimitManager};
use SaQle\Dao\Limit\Limit;

class LimitManager implements ILimitManager{
	 protected ?ILimit $_limit = null;

	 /*setters*/
	 public function set_limit(int $page = 1, int $records = 10){
	 	$this->_limit = new Limit(page: $page, records: $records);
	 }
	 
	 /*getters*/
	 public function get_limit(){
	 	return $this->_limit;
	 }

	 public function construct_limit_clause(){
		 $limit_clause = "";
		 if(!is_null($this->_limit)){
			 $limit_clause = " LIMIT ".$this->_limit->get_offset();
			 $limit_clause .= ", ".$this->_limit->get_records();
		 }
		 return $limit_clause;
	 }
}
?>