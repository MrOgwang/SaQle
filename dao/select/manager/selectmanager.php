<?php
namespace SaQle\Dao\Select\Manager;

use SaQle\Dao\Field\Attributes\{ForeignKey, NavigationKey};
use SaQle\Dao\DbContext\Trackers\DbContextTracker;

class SelectManager implements ISelectManager{
	 protected ?array             $_includes         = null;
	 protected ?array             $_selected         = null;
	 protected ?DbContextTracker  $_context_tracker  = null;
	 public function __construct(){
		 
	 }

	 /*setters*/
	 public function set_context_tracker(?DbContextTracker $context_tracker = null){
	 	$this->_context_tracker = $context_tracker;
	 }
	 public function set_selected(array $selected){
	 	$this->_selected = $selected;
	 }
	 public function add_include($field){
	 	 if(!$this->_includes){
	 		$this->_includes = [];
	 	 }
	 	 $this->_includes[] = $field;
	 }
	 public function get_includes(){
	 	return $this->_includes ? $this->_includes : [];
	 }
	 public function get_selected(){
	 	return $this->_selected ? $this->_selected : [];
	 }
}
?>