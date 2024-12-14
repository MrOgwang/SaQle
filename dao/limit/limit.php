<?php
namespace SaQle\Dao\Limit;

use SaQle\Dao\Limit\Interfaces\ILimit;

class Limit implements ILimit{
	 private int $_page;
	 private int $_records;
	 private int $_offset;
	 public function __construct($page, $records){
		 $this->_page    = $page;
		 $this->_records = $records;
		 $this->set_offset();
	 }
	 
	 private function set_offset(){
		 $this->_offset = ($this->_page - 1) * $this->_records;
	 }

	 public function get_page(){
	 	return $this->_page;
	 }
	 public function get_records(){
	 	return $this->_records;
	 }
	 public function get_offset(){
		 return $this->_offset;
	 }
}
?>