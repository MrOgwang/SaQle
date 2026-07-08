<?php
namespace SaQle\Orm\Query\Limit;

class LimitBuilder {

	 public ?Limit $limit = null {
	 	 set(?Limit $value){
	 	 	 $this->limit = $value;
	 	 }

	 	 get => $this->limit;
	 }

	 public function construct_limit_clause(){
		 $limit_clause = "";
		 if(!is_null($this->limit)){
			 $limit_clause = " LIMIT ".$this->limit->offset;
			 $limit_clause .= ", ".$this->limit->records;
		 }
		 return $limit_clause;
	 }
}
