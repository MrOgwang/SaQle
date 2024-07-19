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
 * Represents a filter manager used when applying filtering on data
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Dao\Filter\Manager;

use SaQle\Dao\Filter\Manager\Interfaces\IFilterManager;
use SaQle\Dao\Filter\Aggregator\Interfaces\IAggregator;
use SaQle\Dao\Filter\Translator\Interfaces\ITranslator;
use SaQle\Dao\Filter\Parser\Interfaces\IParser;
use SaQle\Dao\DbContext\Trackers\Interfaces\IDbContextTracker;

class FilterManager implements IFilterManager{
	public function __construct(
		private IAggregator $aggregator,
		private ITranslator $translator,
		private IParser     $parser
	){}

	public function get_raw_filter(){
		return $this->aggregator->get_raw_filter();
	}

	public function get_filter_object(IDbContextTracker $context_tracker){
		return $this->translator->translate($this->aggregator->get_raw_filter(), $context_tracker)->get_filter();
	}

	public function get_parsed_filter(IDbContextTracker $context_tracker){
		return $this->parser->parse_filters($this->translator->translate($this->aggregator->get_raw_filter(), $context_tracker)->get_filter());
	}

	public function simple_aggregate(array $simple_filter){
		 $this->aggregator->register_filter($simple_filter[0], $simple_filter[1], $simple_filter[2]);
	}

	public function group_aggregate($model_manager, $callback, $operand){
		 #get current rawfilter
		 $current_raw_filter = $this->aggregator->get_raw_filter();
	 	 $current_raw_filter_count = count($current_raw_filter);
	 	 $this->aggregator->initialize();
	 	
	 	 #call the group callback.
	 	 $callback($model_manager);
	 
	 	 #get the new group raw filter
	 	 $new_filters = $model_manager->get_raw_filter();
	 	 
         $and_available = array_search('&', $current_raw_filter);
         $or_available  = array_search('|', $current_raw_filter);
	 	 if($current_raw_filter_count > 0){ #there is already existing raw filter, simple or grouped.
	 	 	 if($or_available || $and_available){
	 	 	 	$current_raw_filter[] = $operand;
	 	 	 	$current_raw_filter[] = $new_filters;
	 	 	 	$this->aggregator->set_raw_filter($current_raw_filter);
	 	 	 }else{
	 	 	 	$this->aggregator->set_raw_filter([$current_raw_filter, $operand, $new_filters]);
	 	 	 }
	 	 }else{
	 	 	 $this->aggregator->set_raw_filter($new_filters);
	 	 }
	 	 $this->aggregator->set_register_count(count($current_raw_filter) > 0 ? 2 : 1);
	}

	public function get_where_clause(IDbContextTracker $context_tracker){
		 #get formatted filter object
	 	 $filter_object = $this->translator->translate($this->aggregator->get_raw_filter(), $context_tracker)->get_filter();
	 	 #parse the filter object.
	 	 $parsed_filter = $this->parser->parse_filters($filter_object);
	 	 /*get and return the where clause*/
	 	 return $this->construct_where_clause($parsed_filter);
	}

	private function construct_where_clause($parsed_filters){
	 	 $clause = "";
		 $data   = null;
	 	 if($parsed_filters["clause"] && $parsed_filters["values"]){
	 	 	 $clause = " WHERE ".$parsed_filters["clause"];
			 $data = [];
			 foreach($parsed_filters["values"] as $d){
				 $data[] = str_replace("'", "", $d);
			 }
	 	 }
		 return (Object)["clause" => $clause, "data" => $data];
	 }
}
?>