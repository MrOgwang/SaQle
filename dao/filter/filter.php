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
 * Represents a filter object used when applying filtering on data
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Dao\Filter;

use SaQle\Dao\Filter\Interfaces\IFilter;

class Filter extends IFilter{
	 protected string $_filter;
	 protected string $_table;
	 protected string $_database;
	 protected bool   $_is_group;
	 protected bool   $_is_literal;
	 public function __construct($filter, $table, $database, $literal){
		 $this->_filter = $filter;
		 $this->_table = $table;
		 $this->_database = $database;
		 $this->_is_literal = $literal;
		 parent::__construct(false);
	 }

	 public function filter(){
	 	return $this->_filter;
	 }
	 public function table(){
	 	return $this->_table;
	 }
	 public function database(){
	 	return $this->_database;
	 }
	 public function literal(){
	 	return $this->_is_literal;
	 }
}
?>