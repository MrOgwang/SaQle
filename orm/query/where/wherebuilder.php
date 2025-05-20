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
 * Represents a where query builder that constructs the where clause for sql statements
 * after collecting simple filters
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Orm\Query\Where;

use SaQle\Orm\Database\Trackers\DbContextTracker;

class WhereBuilder{
	 public protected(set) Aggregator $aggregator {
	 	 set(Aggregator $value){
	 	 	$this->aggregator = $value;
	 	 }

	 	 get => $this->aggregator;
	 }

	 public protected(set) Translator $translator {
	 	 set(Translator $value){
	 	 	$this->translator = $value;
	 	 }

	 	 get => $this->translator;
	 }

	 public protected(set) Parser $parser {
	 	 set(Parser $value){
	 	 	$this->parser = $value;
	 	 }

	 	 get => $this->parser;
	 }

	 public function __construct(){
	 	 $this->aggregator = new Aggregator();
	 	 $this->translator = new Translator();
	 	 $this->parser     = new Parser();
	 }

	 public function simple_aggregate(array $simple_filter){
		 $this->aggregator->register_filter($simple_filter[0], $simple_filter[1], $simple_filter[2], $simple_filter[3]);
	 }

	 public function group_aggregate($model_manager, $callback, $operand){
		 #get current rawfilter
		 $current_raw_filter = $this->aggregator->filter;
	 	 $current_raw_filter_count = count($current_raw_filter);
	 	 $this->aggregator->initialize();
	 	
	 	 #call the group callback.
	 	 $callback($model_manager);
	 
	 	 #get the new group raw filter
	 	 $new_filters = $this->aggregator->filter;
	 	 
         $and_available = array_search('&', $current_raw_filter);
         $or_available  = array_search('|', $current_raw_filter);
	 	 if($current_raw_filter_count > 0){ #there is already existing raw filter, simple or grouped.
	 	 	 if($or_available || $and_available){
	 	 	 	$current_raw_filter[] = $operand;
	 	 	 	$current_raw_filter[] = $new_filters;
	 	 	 	$this->aggregator->filter = $current_raw_filter;
	 	 	 }else{
	 	 	 	$this->aggregator->filter = [$current_raw_filter, $operand, $new_filters];
	 	 	 }
	 	 }else{
	 	 	 $this->aggregator->filter = $new_filters;
	 	 }
	 	 $this->aggregator->counter = count($current_raw_filter) > 0 ? 2 : 1;
	 }

	 public function get_where_clause(DbContextTracker $ctx, array $config){
	 	 $filter_object = $this->translator->translate($this->aggregator->filter, $ctx)->filter;
	 	 $parsed_filter = $this->parser->parse_filters($filter_object, $config);
	 	 return $this->construct_where_clause($parsed_filter);
	 }

	 private function construct_where_clause($parsed_filters){
	 	 $clause = "";
		 $data   = null;
	 	 if($parsed_filters["clause"]){
	 	 	 $clause = " WHERE ".$parsed_filters["clause"];
			 $data = [];
			 foreach($parsed_filters["values"] as $d){
				 $data[] = str_replace("'", "", $d);
			 }
	 	 }
		 return (Object)["clause" => $clause, "data" => $data];
	 }
}
