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
 * Represents a filter object.
 * 
 * Raw filters passed from the client by calling where method on the model manager will be translated into
 * this before they can be used by the query builder.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Orm\Query\Where;

class Filter extends BaseFilter{
	 /**
	  * This is a string denoting a simple filter in the form
	  * column_name~operator~value
	  * */
	 public string $filter = '' {
	 	set(string $value){
	 		$this->filter = $value;
	 	}

	 	get => $this->filter;
	 }

	 /**
	  * The name of the table being manipulated
	  * */
	 public string $table = '' {
	 	set(string $value){
	 		$this->table = $value;
	 	}

	 	get => $this->table;
	 }

	 /**
	  * The name of the database being manipulated
	  * */
	 public string $database = '' {
	 	set(string $value){
	 		$this->database = $value;
	 	}

	 	get => $this->database;
	 }

	 /**
	  * Literal denotes whether a filter column name and value should be taken lietrally
	  * as they are. Literal filters will be insterted into the query wuthout any modifications
	  * such as aliasing column names or fully qualifying them
	  * */
	 public bool $literal = false {
	 	set(bool $value){
	 		$this->literal = $value;
	 	}

	 	get => $this->literal;
	 }

	 public function __construct($filter, $table, $database, $literal){
		 $this->filter   = $filter;
		 $this->table    = $table;
		 $this->database = $database;
		 $this->literal  = $literal;
		 $this->grouped  = false;
	 }
}
