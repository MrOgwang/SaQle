<?php
namespace SaQle\Orm\Query\Join;

use SaQle\Orm\Database\Trackers\DbContextTracker;
use SaQle\Orm\Query\Helpers\Q;

class JoinBuilder {

	 /**
	  * An array of all the joins that have been added to a query
	  * */
	 public array $joins = [] {
	 	 set(array $value){
	 	 	 $this->joins = $value;
	 	 }

	 	 get => $this->joins;
	 }

     /**
      * Register a new join
      * */
	 public function add_join(
	 	 string  $type, 
     	 string  $table, 
     	 ?string $from     = null, 
     	 ?string $to       = null, 
     	 ?string $as       = null, 
     	 ?string $ref      = null,
     	 ?Q      $query    = null,
     	 ?string $database = null
     ){
     	 $joins = $this->joins;
     	 $joins[] = new Join(type: $type, table: $table, from: $from, to: $to, database: $database, aliase: $as, ref: $ref, query: $query);
	 	 $this->joins = $joins;
	 }

	 public function construct_join_clause(DbContextTracker $ctx){

	 	 $clause = "";
	 	 $data   = null;

		 if($this->joins){
			 $join_pieces = [];
			 for($j = 0; $j < count($this->joins); $j++){
			 	 $base_table = $ctx->find_table_name(0);
		         $base_table_aliase = $ctx->aliases[0];
		         $base_database     = $ctx->databases[0];

				 $join     = $this->joins[$j];
				 $aliase   = $join->aliase;
				 $tblref   = $join->ref;
				 $database = $join->database ? $join->database : $base_database;
				 $table    = $join->table;
				 $field_a  = $join->from;
				 $field_b  = $join->to;
				 $type     = $join->type;
				 $query    = $join->query;

				 $realtbl  = $tblref ? $tblref : $database.".".$table;

		         $joining_table_qualified_name = $aliase ? $realtbl." AS ".$aliase : $realtbl;

		         $joining_field_qualified_name = $aliase ? $aliase.".".$field_b : $database.".".$table.".".$field_b;

		         $base_field_qualified_name = $base_table_aliase ? $base_table_aliase.".".$field_a 
		         : $base_database.".".$base_table.".".$field_a;

		         $join_clause = $type." ".$joining_table_qualified_name." ON ".$joining_field_qualified_name." = ".$base_field_qualified_name;

		         if($query){
				 	 $where_clause = $query->wbuilder->get_where_clause($ctx, [], 'join');
				 	 if($where_clause->data){
				 	 	 $data = !$data ? [] : $data;
				 	 	 $data = array_merge($data, $where_clause->data);
				 	 }
				 	 if($where_clause->clause){
				 	 	 $join_clause .= $where_clause->clause;
				 	 }
				 }

		         $join_pieces[] = $join_clause;
			 }
			 $clause = " ".implode(" ", $join_pieces);
		 }

		 return (Object)["clause" => $clause, "data" => $data];
	 }
	 
}
