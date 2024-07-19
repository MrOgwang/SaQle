<?php
namespace SaQle\Dao\Filter;

use SaQle\Dao\Filter\Interfaces\IFilter;

class FilterGroup extends IFilter{
	 protected array  $_filters;
	 protected bool   $_closed;
	 protected bool   $_root;
	 protected string $_group_id;
	 public function __construct($group_id, $root = false, $closed = false, $filters = []){
		 $this->_group_id = $group_id;
		 $this->_root = $root;
		 $this->_closed = $closed;
		 $this->_filters = $filters;
		 parent::__construct(true);
	 }

	 public function is_closed(){
	 	 return $this->_closed;
	 }
	 public function is_root(){
	 	 return $this->_root;
	 }
	 public function group_id(){
	 	return $this->_group_id;
	 }
	 public function filters(){
	 	return $this->_filters;
	 }
	 public function add_filter($filter){
	 	$this->_filters[] = $filter;
	 }
}
?>