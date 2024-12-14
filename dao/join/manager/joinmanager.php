<?php
namespace SaQle\Dao\Join\Manager;

use SaQle\Dao\Join\Interfaces\{IJoin, IJoinManager};
use SaQle\Dao\Join\Join;
use SaQle\Dao\DbContext\Trackers\DbContextTracker;

class JoinManager implements IJoinManager{
	 protected array              $joins           = [];
	 protected ?DbContextTracker  $context_tracker = null;
	 
	 /**
	  * Set the context tracker
	  * */
	 public function set_context_tracker(?DbContextTracker $context_tracker = null){
	 	$this->context_tracker = $context_tracker;
	 }

	 public function add_join(
	 	 string  $type, 
     	 string  $table, 
     	 ?string $from     = null, 
     	 ?string $to       = null, 
     	 ?string $as       = null, 
     	 ?string $ref      = null,
     	 ?array  $select   = null,
     	 ?string $database = null
     ){
     	 $this->joins[] = new Join(
	 		 join_type:           $type,
	 	     join_table:          $table,
	 	     parent_table_field:  $from,
	 	     joining_table_field: $to,
	 	     join_database:       $database,
	 	     join_table_aliase:   $as,
	 	     join_table_ref:      $ref,
	 	     select_columns:      $select
	 	 );
	 }

	 private function get_joins(){
	 	return $this->joins;
	 }

	 public function construct_join_clause(){
		 $join_string = "";
		 $joins = $this->get_joins();
		 if($joins){
			 $join_pieces = [];
			 for($j = 0; $j < count($joins); $j++){
				 $join     = $joins[$j];
				 $aliase   = $join->get_join_table_aliase();
				 $tblref   = $join->get_join_table_ref();
				 $database = $join->get_join_database();
				 $table    = $join->get_join_table();
				 $field_a  = $join->get_parent_table_field();
				 $field_b  = $join->get_joining_table_field();
				 $type     = $join->get_join_type();

				 $realtbl  = $tblref ? $tblref : $database.".".$table;

		         $joining_table_qualified_name = $aliase ? $realtbl." AS ".$aliase : $realtbl;

		         $joining_field_qualified_name = $aliase ? $aliase.".".$field_b : $database.".".$table.".".$field_b;

		         $base_table = $this->context_tracker->find_table_name(0);
		         $base_table_aliase = $this->context_tracker->get_aliases()[0];
		         $base_database     = $this->context_tracker->get_databases()[0];

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