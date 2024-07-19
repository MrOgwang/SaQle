<?php
namespace SaQle\Dao\Join;
class Join extends IJoin{
	 public function __construct(
	 	 private string $_join_type,
	 	 private string $_join_table,
	 	 private string $_parent_table_field,
	 	 private string $_joining_table_field,
	 	 private string $_join_database,
	 	 private ?string $_join_table_aliase = null,
 	 ){
		 
	 }

	 public function get_join_type(){
	 	return $this->_join_type;
	 }
	 public function get_join_table(){
	 	return $this->_join_table;
	 }
	 public function get_join_table_aliase(){
	 	return $this->_join_table_aliase;
	 }
	 public function get_parent_table_field(){
	 	return $this->_parent_table_field;
	 }
	 public function get_joining_table_field(){
	 	return $this->_joining_table_field;
	 }
	 public function get_join_database(){
	 	return $this->_join_database;
	 }
}
?>