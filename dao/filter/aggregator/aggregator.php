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
 * Represents a filter aggregator object: The aggregator collects raw filters
 * from clients calling where, or_where, gwhere and or_gwhere on the model manager.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Dao\Filter\Aggregator;

use SaQle\Dao\Filter\Aggregator\Interfaces\IAggregator;

class Aggregator implements IAggregator{
	 /**
	  * A cumulative array of filter data as they are received from the client calling
	  * where, or_where, gwhere and or_gwhere methods of model manager.
	  * 
	  * A simple raw_filter has two elements: 
	  * 0 - String field name
	  * 1 - Mixed field value
	  * Example: ['id__eq', 10]
	  * 
	  * A grouped raw_filter has simple raw_filter elements alternating with string logical operands.
	  * Example: [['age__gt', 18], '&', ['cars__gt', 2]]
	  * */
	 protected array $raw_filter = [];

	 /**
	  * How many simple filters have been registered for the same query instance.
	  * A single filter is registered with the where, or_where, gwhere and or_gwhere method calls on the model manager
	  * */
	 protected int $register_count = 0;


	 /**
	  * Initialize the raw_filter and register_count values.
	  * */
	 public function initialize(){
	 	 $this->register_count = 0;
	 	 $this->raw_filter     = [];
	 }

     /**
      * Set the register count.
      * @param int
      * */
	 public function set_register_count(int $count){
	 	 $this->register_count = $count;
	 }

	 /**
	  * Set the raw filter
	  * @param array: as described above
	  * */
	 public function set_raw_filter(array $filter){
	 	$this->raw_filter = $filter;
	 }

	 /**
	  * Return the raw filter
	  * @return array
	  * */
	 public function get_raw_filter() : array{
		 return $this->raw_filter;
	 }

	 /**
	  * Return the register count
	  * @return int
	  * */
	 public function get_register_count() : int{
	 	 return $this->register_count;
	 }

     /**
      * This is the interface used by the model manager to register simple filters
      * with where and or_where methods.
      * @param string field_name: the name of the field
      * @param mixed  value : the value of the field
      * @param nullable string operator : a string logical operator represented by | or & characters
      * */
	 public function register_filter(string $field_name, mixed $value, ?string $operator = null){
         $operator = $operator ?? "&";
	 	 #Ignore the operator if this is the first filter registered.
	 	 if($this->register_count === 0){
	 	 	 $this->raw_filter[] = $field_name;
	 	     $this->raw_filter[] = $value;
	 	 }else{
	 	 	 #subsequent simple filters will make the current raw filter a grouped filter.
	 	 	 if($this->register_count == 1){
	 	 	 	 $current_filter = $this->raw_filter;
	 		     $this->raw_filter = [$current_filter];
	 	 	 }
	 	 	 $this->raw_filter[] = $operator;
	 	 	 $incoming_simple  = [$field_name, $value];
		 	 $this->raw_filter[] = $incoming_simple;
	 	 }
	 	 
	 	 $this->register_count++;

	 	 return;
	 }
}
?>