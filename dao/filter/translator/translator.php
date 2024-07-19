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
namespace SaQle\Dao\Filter\Translator;

use SaQle\Dao\Filter\Translator\Interfaces\ITranslator;
use SaQle\Dao\Filter\Interfaces\IFilter;
use SaQle\Dao\Filter\{Filter, FilterGroup};
use SaQle\Dao\DbContext\Trackers\Interfaces\IDbContextTracker;

class Translator implements ITranslator{
	 protected ?IFilter $filter = null;
	 protected ?IDbContextTracker $context_tracker  = null;

	 public function get_filter() : IFilter{
	 	return $this->filter;
	 }

	 private function get_guid($max_length = 30, $min_length = 30){
		 $min_length = $max_length < $min_length ? $max_length : $min_length;
         $half_min = ceil($min_length / 2);
         $half_max = ceil($max_length / 2);
         $bytes = random_bytes(rand($half_min, $half_max));
         $random_string = bin2hex($bytes);
         $random_string = strlen($random_string) > $max_length ? substr($random_string, 0, -1) : $random_string;
         return $random_string;
	 }

	 public function translate(array $raw_filters, IDbContextTracker $context_tracker){
		 $this->context_tracker = $context_tracker;
		 return $this->filter($raw_filters);
	 }

	 private function filter(array $filters, string $operand = null, string$pgid = null, bool $fill_blanks = true){
		 if(count($filters) === 0){
			 return $this;
		 }
		 
		 $topgroup_created = false;
		 if(!$operand && !$pgid){
		 	 $topgroup_created = true;
			 $pgid = $this->get_guid(max_length: 10, min_length: 10);
			 $this->filter = new FilterGroup(group_id: $pgid, root: true);
		 }
		 $is_group = $this->is_group(filters: $filters);
		 if($is_group){
		 	 if(!$topgroup_created){
		 	 	 //create new group
			     $group_id = $this->get_guid(max_length: 10, min_length: 10);
			     $this->insert_into_parent(pgid: $pgid, filter_entity: new FilterGroup(group_id: $group_id), operand: $operand);
		 	 }else{
		 	 	$group_id = $pgid;
		 	 }
			 if(count($filters) >= 3){
				 foreach($filters as $index => $f){
					 if($index % 2 === 0){
						 $this->filter(filters: $f, operand: $filters[$index + 1] ?? null, pgid: $group_id, fill_blanks: $fill_blanks);
					 }
				 }
			 }else{
				 $this->filter(filters: $filters[0], pgid: $group_id, fill_blanks: $fill_blanks);
			 }
		 }else{
			 $filter_group = $this->do_filter(filter: $filters, fill_blanks: $fill_blanks);
			 if($filter_group){
				 $this->insert_into_parent(pgid: $pgid, filter_entity: $filter_group, operand: $operand);
			 }
		 }
		 return $this;
	 }

	 private function find_lookup_arithmetic_operator($lookup_type, $field_value = null, $strict_null = false){
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
		 return ["operator" => $lookups[$lookup_type], "field_value"=>$this->transform_field_value(lookup: $lookup_type, value: $field_value, strict: $strict_null)];
	 }

	 private function transform_field_value($lookup, $value = null, $strict = false){
		 if(!is_null($value)){
			 switch($lookup){
			 	 case "eq":
				 case "exact":
				 case "iexact":
				 case "gt":
				 case "gte":
				 case "lt":
				 case "lte":
				 case "ne":
				     //value doesnt change
				 break;
				 case "contains":
				 case "icontains":
				     $value = "%".$value."%";
				 break;
				 case "in":
				     if(is_string($value)) $value = str_split($value);
					 if(!is_array($value)) $value = [$value];
					 if(count($value) === 0){
						 $value[] = 0;
						 $value[] = 0;
					 }
					 $value = "(" .implode(", ", $value).")";
				 break;
				 case "startswith":
				 case "istartswith":
				     $value = $value."%";
				 break;
				 case "endswith":
				 case "iendswith":
				     $value = "%".$value;
				 break;
				 case "range":
				     $value =  $value[0]." and ".$value[1];
				 break;
				 case "isnull":
				     $value =  "";
				 break;
			 }
		 }
		 return $value;
	 }

	 /*
	     Determine if a filter array is a grouped filter
	 */
	 private function is_group(array $filters){
		 for($e = 0; $e < count($filters); $e++){
			 if($e % 2 !== 0 && (is_array($filters[$e]) || !in_array($filters[$e], ["&", "|"]))){
				 return false;
			 }
		 }
		 return true;
	 }

	 /*
	     Insert a filter object into parent group
	 */
	 public function insert_into_parent(string $pgid, IFilter $filter_entity, IFilter $pg = null, string $operand = null){
		 $pg = $pg ?? $this->filter;
		 if($pg->group_id() === $pgid){
		 	 $pg->add_filter($filter_entity);
			 if($operand)
			 	 $pg->add_filter($operand);
			 return true;
		 }else{
			 for($x = 0; $x < count($pg->filters()); $x++){
				 $current_pg = $pg->filters()[$x];
				 if( is_object($current_pg) && str_contains(get_class($current_pg), "FilterGroup") && $current_pg->is_group() ){
					 $this->insert_into_parent(pgid: $pgid, filter_entity: $filter_entity, pg: $current_pg, operand: $operand);
				 }
			 }
		 }
		 $this->filter = $pg;
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
		 $component_indexes = array(0, 1, 2);
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

	 public function fill_in_blanks(array $filter, $strict_null = false){
		 $new_filter = [null, null, null, null, null, null];
		 $filter[0]  = is_array($filter[0]) ? $filter[0] : [$filter[0]];
		 $filter[2]  = $filter[2] ?? "&";
		 //expand new_filter.
		 $filter = $this->expand_filter(filter: $filter);
		 foreach($filter as $index => $el){
			 if($index === 0) $new_filter[0] = $el;
			 if($index === 1) $new_filter[2] = $el;
			 if($index === 2) $new_filter[3] = $el;
		 }
		 $operators = [];
		 $tables    = [];
		 $databases = [];
		 foreach($filter[0] as $index => $field){
			 $field_properties = explode("__", $field);
			 if(count($field_properties) === 1) array_push($field_properties, "exact");
			 $base_table        = $this->context_tracker->find_table_name(0);
			 $tbl_index_search  = $this->context_tracker->find_table_index($base_table, $field);
			 $tbl_index         = $tbl_index_search['table_index'];
			 if($tbl_index_search['name_changed']){
			 	$base_table          = $this->context_tracker->find_table_name($tbl_index);
			 	$field_properties[0] = explode(":", $field)[1];
			 }
			 $new_filter[0][$index] = explode("__", $field_properties[0])[0];
			 $lap                   = $this->find_lookup_arithmetic_operator(lookup_type: $field_properties[1], field_value: $filter[1][$index], strict_null: $strict_null);

			 $new_filter[2][$index] = $lap["field_value"];
			 $operators[]           = $lap["operator"];
			 $tables[]              = $this->context_tracker->find_table_name($tbl_index);
			 $databases[]           = $this->context_tracker->find_database_name($tbl_index);
		 }
		 $new_filter[1] = $operators;
		 $new_filter[4] = $tables;
		 $new_filter[5] = $databases;
		 return $new_filter;
	 }

	 private function do_filter($filter, $fill_blanks = true){
		 $new_group = null;
		 $filter_object = null;
		 if(isset($filter[0]) && isset($filter[1])){
			 /*$new_group = new FilterGroup(
			 	 group_id: $this->get_guid(max_length: 10, min_length: 10), 
			 	 root:     false,
			 	 closed:   true
			 );*/
			 if($fill_blanks){
				 $filter = $this->fill_in_blanks(filter: $filter, strict_null: true);
			 }
			 for($x = 0; $x < count($filter[0]); $x++){
				 $basic_filter  = $filter[0][$x]."~".$filter[1][$x]."~".$filter[2][$x];
				 $filter_object = new Filter(
				 	 filter:   $basic_filter,
				 	 table:    $filter[4][$x],
				 	 database: $filter[5][$x]
				 );
				 //array_push($new_group->filters(), $filter_object);
				 //$new_group->add_filter($filter_object);
				 if($x < count($filter[0]) - 1){
					 //$new_group->filters()[] = $filter[3][$x];
					 //$new_group->add_filter($filter[3][$x]);
				 }
			 }
		 }
		 //return $new_group;
		 return $filter_object;
	 }
}
?>