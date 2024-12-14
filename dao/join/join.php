<?php
namespace SaQle\Dao\Join;

use SaQle\Dao\Join\Interfaces\IJoin;

class Join implements IJoin{
	 public function __construct(
	 	 private string  $join_type,
	 	 private string  $join_table,
	 	 private string  $parent_table_field,
	 	 private string  $joining_table_field,
	 	 private string  $join_database,
	 	 private ?string $join_table_aliase  = null,
	 	 private ?string $join_table_ref     = null,
	 	 private ?array  $select_columns     = null
 	 ){}

	 public function get_join_type(){
	 	return $this->join_type;
	 }

	 public function get_join_table(){
	 	return $this->join_table;
	 }

	 public function get_join_table_aliase(){
	 	return $this->join_table_aliase;
	 }

	 public function get_parent_table_field(){
	 	return $this->parent_table_field;
	 }

	 public function get_joining_table_field(){
	 	return $this->joining_table_field;
	 }

	 public function get_join_database(){
	 	return $this->join_database;
	 }

	 public function get_join_table_ref(){
	 	 return $this->join_table_ref;
	 }

	 public function get_select_columns(){
	 	 return $this->select_columns;
	 }
}
?>