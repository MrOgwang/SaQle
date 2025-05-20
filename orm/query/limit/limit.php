<?php
namespace SaQle\Orm\Query\Limit;

class Limit{
	 //The page number
	 public private(set) int $page {
	 	 set(int $value){
	 	 	 $this->page = $value;
	 	 }

	 	 get => $this->page;
	 }

     //the number of records to return
	 public private(set) int $records {
	 	 set(int $value){
	 	 	 $this->records = $value;
	 	 }

	 	 get => $this->records;
	 }

     //the offset
	 public private(set) int $offset {
	 	 set(int $value){
	 	 	 $this->offset = $value;
	 	 }

	 	 get => $this->offset;
	 }

	 public function __construct($page, $records){
		 $this->page    = $page;
		 $this->records = $records;
		 $this->set_offset();
	 }
	 
	 private function set_offset(){
		 $this->offset = ($this->page - 1) * $this->records;
	 }
}
