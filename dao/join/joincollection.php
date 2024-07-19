<?php
declare(strict_types = 1);
namespace SaQle\Dao\Join;

class JoinCollection extends IJoin{
	 function __construct(private array $joins = []){

	 }
	 public function add(IJoin $join){
		 $this->joins[] = $join;
	 }
	 public function get_joins(){
		 return $this->joins;
	 }
}

?>