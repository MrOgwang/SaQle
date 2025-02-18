<?php
namespace SaQle\Dao\Group\Manager;

use SaQle\Dao\DbContext\Trackers\DbContextTracker;
use SaQle\Dao\Group\Interfaces\IGroupManager;

class GroupManager implements IGroupManager{
	 protected ?array             $_fields          = null;
	 protected ?DbContextTracker  $_context_tracker = null;
	 public function __construct(){
	 }

	 /*setters*/
	 public function set_context_tracker(?DbContextTracker $context_tracker = null){
	 	$this->_context_tracker = $context_tracker;
	 }
	 public function set_groupby(?array $fields = null){
	 	$this->_fields = $fields;
	 }
	 public function get_groupby(...$config){
	 	if(!$this->_fields)
	 		return [];

	 	if($config['fnqm'] === 'N-QUALIFY')
     	 	return $this->_fields;

	 	$tables    = $this->_context_tracker->get_tables();
	 	$aliases   = $this->_context_tracker->get_aliases();
	 	$databases = $this->_context_tracker->get_databases();
	 	$fieldrefs = $this->_context_tracker->get_fieldrefs();
	 	
	 	$tmp_fields = $this->_fields;
 		$this->_fields = [];
 		foreach($tmp_fields as $cn){
 			 //is the column name a fully qualified name?
 			 $cn_parts = explode(".", $cn);
 			 if(count($cn_parts) === 3){
 			 	 $this->_fields[] = $cn;
 			 	 continue;
 			 }

 			 //is the column name half qualified name.
 			 if(count($cn_parts) === 2){
 			 	 $table_name = $cn_parts[0];
 			 	 $table_index = array_search($table_name, $tables);
 			 	 if($table_index !== false){
 			 	 	 $this->_fields[] = $databases[$table_index].".".$cn;
 			 	 }
 			 	 continue;
 			 }

 			 //is the column name not qualified at all?
 			 $cg_index = -1;
 			 foreach($fieldrefs as $t_index => $fields){
 			 	 $ci = array_search($cn, $fields);
 			 	 if($ci !== false){
 			 	 	 $cg_index = $t_index;
 			 	 	 break;
 			 	 }
 		     }

 		     if($cg_index !== -1){
 		     	 $this->_fields[] = $aliases[$cg_index] ? $databases[$cg_index].".".$aliases[$cg_index].".".$cn : $databases[$cg_index].".".$tables[$cg_index].".".$cn;
 		     }
 		}

	 	return $this->_fields;
	 }
}
?>