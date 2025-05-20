<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * Represents a filter translater object: The translator takes in raw aggregated filters and turns into
 * a filter object.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Orm\Query\Where;

use SaQle\Orm\Database\Trackers\DbContextTracker;

class Translator{
	 public protected(set) ?FilterGroup $filter = null {
	 	 set(?FilterGroup $value){
	 	 	$this->filter = $value;
	 	 }

	 	 get => $this->filter;
	 }
	 
	 private function get_guid($max_length = 30, $min_length = 30){
		 $min_length    = $max_length < $min_length ? $max_length : $min_length;
         $half_min      = ceil($min_length / 2);
         $half_max      = ceil($max_length / 2);
         $bytes         = random_bytes(rand($half_min, $half_max));
         $random_string = bin2hex($bytes);
         $random_string = strlen($random_string) > $max_length ? substr($random_string, 0, -1) : $random_string;
         return $random_string;
	 }

	 public function translate(array $filters, DbContextTracker $ctx){
		 return $this->filter($filters, $ctx);
	 }

     //check whether filters is a simple filter or complex filter
	 private function is_group(array $filters){
		 for($e = 0; $e < count($filters); $e++){
			 if($e % 2 !== 0 && (is_array($filters[$e]) || !in_array($filters[$e], ["&", "|"]))){
				 return false;
			 }
		 }
		 return true;
	 }

	 private function do_filter($filter, $ctx, $fill_blanks = true){
	 	 if(!isset($filter[0]) || !isset($filter[1]))
	 	 	 return null;
         
         $filter_object = null;
		 if($fill_blanks)
			 $filter = $this->fill_in_blanks(filter: $filter, ctx: $ctx, strict_null: true);
			 
		 for($x = 0; $x < count($filter[0]); $x++){
			 $basic_filter  = $filter[0][$x]."~".$filter[1][$x]."~".$filter[2][$x];
			 $filter_object = new Filter(
			 	 filter:   $basic_filter,
			 	 table:    $filter[4][$x],
			 	 database: $filter[5][$x],
			 	 literal:  is_array($filter[6]) ? $filter[6][$x] : $filter[6]
			 );
		 }

		 return $filter_object;
	 }

	 //Insert a filter object into parent group
	 private function insert_into_parent(string $pgid, $filter_entity, $pg = null, ?string $operand = null){
		 $pg = $pg ?? $this->filter;
		 if($pg->group_id === $pgid){
		 	 $pg->add_filter($filter_entity);
			 if($operand)
			 	 $pg->add_filter($operand);
			 return true;
		 }

		 for($x = 0; $x < count($pg->filters); $x++){
			 $current_pg = $pg->filters[$x];
			 if( is_object($current_pg) && str_contains(get_class($current_pg), "FilterGroup") && $current_pg->grouped ){
				 $this->insert_into_parent(pgid: $pgid, filter_entity: $filter_entity, pg: $current_pg, operand: $operand);
			 }
		 }

		 $this->filter = $pg;
		 return true;
	 }

	 private function filter(array $filters, DbContextTracker $ctx, ?string $operand = null, ?string $pgid = null, bool $fill_blanks = true){
		 if(count($filters) === 0)
			 return $this;
		 
		 $topgroup_created = false;
		 if(!$operand && !$pgid){
		 	 $topgroup_created = true;
			 $pgid             = $this->get_guid(max_length: 10, min_length: 10);
			 $this->filter     = new FilterGroup(group_id: $pgid, root: true);
		 }

		 $is_group = $this->is_group(filters: $filters);
		 if($is_group){

		 	 $group_id = $pgid;
		 	 if(!$topgroup_created){
			     $group_id = $this->get_guid(max_length: 10, min_length: 10);
			     $this->insert_into_parent(pgid: $pgid, filter_entity: new FilterGroup(group_id: $group_id), operand: $operand);
		 	 }

			 if(count($filters) >= 3){
				 foreach($filters as $index => $f){
					 if($index % 2 === 0){
						 $this->filter(filters: $f, ctx: $ctx, operand: $filters[$index + 1] ?? null, pgid: $group_id, fill_blanks: $fill_blanks);
					 }
				 }

				 return $this;
			 }

			 $this->filter(filters: $filters[0], ctx: $ctx, pgid: $group_id, fill_blanks: $fill_blanks);
			 return $this;
		 }

		 $filter_group = $this->do_filter(filter: $filters, ctx: $ctx, fill_blanks: $fill_blanks);
		 if($filter_group)
			 $this->insert_into_parent(pgid: $pgid, filter_entity: $filter_group, operand: $operand);

		 return $this;
	 }

     private function get_in_value($value, $literal){
         if($literal)
         	return "(".$value.")";

     	 if(is_string($value)) $value = str_split($value);

		 if(!is_array($value)) $value = [$value];

		 if(count($value) === 0){
			 $value[] = 0;
			 $value[] = 0;
		 }
			 
	     return "(".implode(", ", $value).")";
     }

     private function transform_field_value($lookup, $value = null, $strict = false, $literal = 0){
     	 if(is_null($value))
     	 	 return $value;

     	 return match($lookup){
     	 	 'contains', 'icontains'     => "%".$value."%",
     	 	 'startswith', 'istartswith' => $value."%",
     	 	 'endswith', 'iendswith'     => "%".$value,
     	 	 'range'                     => $value[0]." and ".$value[1],
     	 	 'isnull'                    => '',
     	 	 'in'                        => $this->get_in_value($value, $literal),
     	 	 default                     => $value
     	 };
	 }

	 private function find_lookup_arithmetic_operator($lookup_type, $field_value = null, $strict_null = false, $literal = 0){
		 $lookups = [
		     "exact"       => $strict_null && is_null($field_value) ? "IS null"     : "=", 
			 "ne"          => $strict_null && is_null($field_value) ? "IS NOT null" : "!=", 
			 "iexact"      => $strict_null && is_null($field_value) ? "IS null"     : "LIKE",
			 "contains"    => "LIKE",
			 "icontains"   => "LIKE", 
			 "in"          => "IN", 
			 "gt"          => ">", 
			 "gte"         => ">=", 
		     "lt"          => "<", 
			 "lte"         => "<=", 
			 "startswith"  => "LIKE", 
			 "istartswith" => "LIKE", 
			 "endswith"    => "LIKE", 
			 "iendswith"   => "LIKE", 
			 "range"       => "BETWEEN", 
			 "isnull"      => "IS null",
			 "eq"          => "="
		 ];
		 return ["operator" => $lookups[$lookup_type], "field_value" => $this->transform_field_value(
		 	 lookup: $lookup_type, value: $field_value, strict: $strict_null, literal: $literal
		 )];
	 }

	 private function expand_filter(array $filter){
		 if(count($filter[0]) === 1){
			 $field_properties = explode("__", $filter[0][0]);
			 if(count($field_properties) > 1 && $field_properties[1] == "in"){
			 	 $filter[1] = [$filter[1]];
				 return $filter;
			 }
		 }
		 $max_count = max([is_array($filter[0]) ? count($filter[0]) : 1, is_array($filter[1]) ? count($filter[1]) : 1]);
		 $component_indexes = array(0, 1, 2, 3);
		 foreach($component_indexes as $index){
			 $filter[$index] = is_array($filter[$index]) ? $filter[$index] : [$filter[$index]];
			 if(count($filter[$index]) < $max_count){
				 $last_element = $filter[$index][count($filter[$index]) - 1];
				 $rem = $max_count - count($filter[$index]);
				 for($c = 0; $c < $rem; $c++){
					 array_push($filter[$index], $last_element);
				 }
			 }
		 }
		 return $filter;
	 }

	 private function fill_in_blanks(array $filter, DbContextTracker $ctx, $strict_null = false){
	 	 /**
	 	  * a c omplete filter array has the following elements
	 	  * 0 - the name of the column
	 	  * 1 - the comparison operator =, <=, >=, !=
	 	  * 2 - the value of column
	 	  * 3 - the logical operator &, |
	 	  * 4 - the name of the table
	 	  * 5 - the name of the database
	 	  * 6 - whether filter is literal
	 	  * */
		 $new_filter = [null/*column*/, null/*c op*/, null/*val*/, null/*l op*/, null/*table*/, null/*database*/, null/*literal*/];
		 $filter[0]  = is_array($filter[0]) ? $filter[0] : [$filter[0]]; 
		 if(count($filter) === 2){ //filter has no literal flag
		 	 $filter[2] = 0; //literal is off by default
		 }
		 $filter[3]  = $filter[3] ?? "&"; //add in a default logical operator if there is non
		 //expand new_filter.
		 $filter = $this->expand_filter(filter: $filter);
		 foreach($filter as $index => $el){
			 if($index === 0) $new_filter[0] = $el;
			 if($index === 1) $new_filter[2] = $el;
			 if($index === 3) $new_filter[3] = $el;
			 if($index === 2) $new_filter[6] = $el;
		 }
		 $operators = [];
		 $tables    = [];
		 $databases = [];
		 foreach($filter[0] as $index => $field){
			 $field_properties = explode("__", $field);
			 if(count($field_properties) === 1) array_push($field_properties, "exact");
			 $base_table        = $ctx->find_table_name(0);
			 $tbl_index_search  = $ctx->find_table_index($base_table, $field);
			 $tbl_index         = $tbl_index_search['table_index'];
			 if($tbl_index_search['name_changed']){
			 	$base_table          = $ctx->find_table_name($tbl_index);
			 	$field_properties[0] = explode(":", $field)[1];
			 }
			 $new_filter[0][$index] = explode("__", $field_properties[0])[0];
			 $lap                   = $this->find_lookup_arithmetic_operator(
			 	 lookup_type: $field_properties[1], 
			 	 field_value: $filter[1][$index],
			 	 strict_null: $strict_null,
			 	 literal:     is_array($filter[2]) ? $filter[2][$index] : $filter[2]
			 );

			 $new_filter[2][$index] = $lap["field_value"];
			 $operators[]           = $lap["operator"];
			 $tables[]              = $ctx->find_table_name($tbl_index);
			 $databases[]           = $ctx->find_database_name($tbl_index);
		 }
		 $new_filter[1] = $operators;
		 $new_filter[4] = $tables;
		 $new_filter[5] = $databases;
		 return $new_filter;
	 }
}
