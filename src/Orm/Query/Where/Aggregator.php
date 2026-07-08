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
 * Represents a filter aggregator object: 
 * 
 * The aggregator collects raw filters from client code
 * 
 * A raw filter is passed by the client code when it calls the methods: where, or_where, gwhere and or_gwhere on the model manager.
 * 
 * Example: Suppose inside a controller we want to get all the users who are 18 and above from a users table, we would do it this way:
 * User::get()->where('age__gte', 18)->all();
 * 
 * The where method in that construct passes a raw filter, which is collected by the aggregator.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Orm\Query\Where;

class Aggregator{
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
	 public array $filter = []{
	 	 set(array $value){
	 	 	$this->filter = $value;
	 	 }

	 	 get => $this->filter;
	 }

	 /**
	  * How many simple filters have been registered for the same query instance.
	  * A single filter is registered with the where, or_where, gwhere and or_gwhere method calls on the model manager
	  * */
	 public int $counter = 0 {
	 	 set(int $value){
	 	 	$this->counter = $value;
	 	 }

	 	 get => $this->counter;
	 }

	 /**
	  * Initialize the filter and counter values.
	  * */
	 public function initialize(){
	 	 $this->counter = 0;
	 	 $this->filter  = [];
	 }

     /**
      * This is the interface used by the model manager to register simple filters
      * with where and or_where methods.
      * @param string   field_name      : the name of the field
      * @param mixed    value           : the value of the field
      * @param int      literal         : whether this is a lietral filter or not
      * @param nullable string operator : a string logical operator represented by | or & characters
      * */
	 public function register_filter(string $field_name, mixed $value, int $literal = 0, ?string $operator = null){
         $operator = $operator ?? "&";
	 	 //Ignore the operator if this is the first filter registered.
	 	 if($this->counter === 0){
	 	     $this->filter = [$field_name, $value, $literal];
	 	 }else{
	 	 	 //subsequent simple filters will make the current filter a grouped filter.
	 	 	 $grouped = $this->filter;
	 	 	 if($this->counter == 1){
	 		     $this->filter = [$grouped, $operator, [$field_name, $value, $literal]];
	 	 	 }else{
	 	 	 	 $grouped[]    = $operator;
	 	 	 	 $grouped[]    = [$field_name, $value, $literal];
	 	 	 	 $this->filter = $grouped;
	 	 	 }
	 	 }
	 	 
	 	 $this->counter++;
	 	 
	 	 return;
	 }
}
