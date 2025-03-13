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
 * Represents a filter group object.
 * 
 * Raw filters passed from the client by calling where method on the model manager will be translated into
 * this before they can be used by the query builder.
 * 
 * When many simple filters are combined, they form a filter group which is represented
 * by this object
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Orm\Query\Where;

class FilterGroup extends BaseFilter{
	 /**
	  * An array of many simple filter objects
	  * */
	 public array $filters = [] {
	 	 set(array $value){
	 	 	$this->filters = $value;
	 	 }

	 	 get => $this->filters;
	 }

     /**
      * Whether this group is closed or open. A closed group will not receive any more
      * simple filters.
      * */
     public bool $closed = false {
     	 set(bool $value){
	 	 	$this->closed = $value;
	 	 }

	 	 get => $this->closed;
     }

     /**
      * Whether this is the root group filter or not.
      * */
     public bool $root = false {
     	 set(bool $value){
	 	 	$this->root = $value;
	 	 }

	 	 get => $this->root;
     }

     /**
      * A string guid that uniquely identifies this group
      * */
     public string $group_id = '' {
     	 set(string $value){
	 	 	$this->group_id = $value;
	 	 }

	 	 get => $this->group_id;
     }

	 public function __construct($group_id, $root = false, $closed = false, $filters = []){
		 $this->group_id = $group_id;
		 $this->root     = $root;
		 $this->closed   = $closed;
		 $this->filters  = $filters;
		 $this->grouped  = true;
	 }

	 public function add_filter($filter){
	 	 $filters       = $this->filters;
	 	 $filters[]     = $filter;
	 	 $this->filters = $filters;
	 }
}
?>