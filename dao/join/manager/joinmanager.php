<?php
namespace SaQle\Dao\Join\Manager;

use SaQle\Dao\Join\IJoin;
use SaQle\Dao\Join\Join;
use SaQle\Dao\Join\JoinCollection;
use SaQle\Dao\DbContext\Trackers\DbContextTracker;

class JoinManager extends IJoinManager{
	 protected ?IJoin                 $_join             = null;
	 protected ?DbContextTracker  $_context_tracker  = null;
	 public function __construct(){
		 
	 }
	 /*setters*/
	 public function set_context_tracker(?DbContextTracker $context_tracker = null){
	 	$this->_context_tracker = $context_tracker;
	 }
	 public function add_join(string $type, string $table, ?string $from = null, ?string $to = null, ?string $as = null, ?string $database = null){
	 	 //$table = $as ? $table." AS ".$as : $table;
	 	 //if $from is not provided, get the primary key for parent table.
	 	 //if $to is not provided, get the primary key of parent table.
	 	 $new_join = new Join(
	 		 _join_type:           $type,
	 	     _join_table:          $table,
	 	     _parent_table_field:  $from,
	 	     _joining_table_field: $to,
	 	     _join_database:       $database,
	 	     _join_table_aliase:   $as
	 	 );
	 	 if(!$this->_join){
	 		$this->_join = new JoinCollection();
	 	 }
	 	 $this->_join->add($new_join);
	 }
	 private function get_joins(){
	 	return $this->_join ? $this->_join->get_joins() : [];
	 }
	 public function construct_join_clause(){
		 $join_string = "";
		 $joins = $this->get_joins();
		 if($joins){
			 $join_pieces = [];
			 for($j = 0; $j < count($joins); $j++){
				 $join     = $joins[$j];
				 $aliase   = $this->_context_tracker->get_aliases()[$j + 1];
				 $database = $join->get_join_database();
				 $table    = $join->get_join_table();
				 $field_a  = $join->get_parent_table_field();
				 $field_b  = $join->get_joining_table_field();
				 $type     = $join->get_join_type();

		         $joining_table_qualified_name = $aliase ? $database.".".$table." AS ".$aliase : $database.".".$table;

		         $joining_field_qualified_name = $aliase ? $aliase.".".$field_b : $database.".".$table.".".$field_b;

		         $base_table                   = $this->_context_tracker->find_table_name(0);
			     $base_table_index_search      = $this->_context_tracker->find_table_index($base_table, $field_a);
			     $base_table_index             = $base_table_index_search['table_index'];
			     if($base_table_index_search['name_changed']){
			 	     $base_table           = $this->_context_tracker->find_table_name($base_table_index);
			 	     $field_a              = explode(":", $field_a)[1];
			     }
				 $base_table_aliase = $this->_context_tracker->get_aliases()[$base_table_index];
		         $base_database     = $this->_context_tracker->get_databases()[$base_table_index];

		         $base_field_qualified_name = $base_table_aliase ? $base_table_aliase.".".$field_a 
		         : $base_database.".".$base_table.".".$field_a;
		         $join_pieces[] = $type." ".$joining_table_qualified_name." ON ".$joining_field_qualified_name." = ".$base_field_qualified_name;
			 }
			 $join_string = " ".implode(" ", $join_pieces);
		 }
		 return $join_string;
	 }

}
?>