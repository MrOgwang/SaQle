<?php
namespace SaQle\Dao\Select\Manager;

use SaQle\Dao\Field\Attributes\{ForeignKey, NavigationKey};
use SaQle\Dao\DbContext\Trackers\DbContextTracker;
use SaQle\Dao\Select\Interfaces\ISelectManager;

class SelectManager implements ISelectManager{
	 protected ?array             $_includes         = null;
	 protected ?array             $_selected         = null;
	 protected ?DbContextTracker  $_context_tracker  = null;

	 /**
 	 * When selecting included fields, this keeps a record of the callbacks provided to fine tune the results
 	 * of an include field. This is a key => value array where key is the field name and value 
 	 * is the callback
 	 * */
 	 private array $withcallbacks = [];

	 public function __construct(){
	 }

	 /*setters*/
	 /**
	  * Set with callbacks
	  * */
	 public function set_withcallbacks(array $callbacks){
	 	 $this->withcallbacks = $callbacks;
	 }
	 public function set_context_tracker(?DbContextTracker $context_tracker = null){
	 	$this->_context_tracker = $context_tracker;
	 }
	 public function set_selected(?array $selected = null){
	 	$this->_selected = $selected;
	 }
	 public function add_include($field){
	 	 if(!$this->_includes){
	 		$this->_includes = [];
	 	 }
	 	 $this->_includes[] = $field;
	 }

	 /**
      * Get with callbacks
      * */
     public function get_withcallbacks(){
     	 return $this->withcallbacks;
     }
	 public function get_includes(){
	 	return $this->_includes ? $this->_includes : [];
	 }
	 private function get_fq_selected($table, $aliase, $database, $fields){
	 	 return array_map(function($f) use ($table, $aliase, $database){
		     #if there is a table aliase, don't include the database name in the fully qualified name.
		     return $aliase ? $aliase.".".$f : $database.".".$table.".".$f;
		 }, $fields);
	 }
	 private function get_hq_selected($table, $aliase, $fields){
	 	 return array_map(function($f) use ($table, $aliase, $database){
		     return $aliase ? $aliase.".".$f : $table.".".$f;
		 }, $fields);
	 }
	 /*public function get_selected(...$config){
	 	
	 	$tables    = $this->_context_tracker->get_tables();
	 	$aliases   = $this->_context_tracker->get_aliases();
	 	$databases = $this->_context_tracker->get_databases();
	 	$fieldrefs = $this->_context_tracker->get_fieldrefs();

	 	if(!$this->_selected){
	 		 $this->_selected = [];
	 		 foreach($fieldrefs as $t_index => $fields){
	 		 	 $this->_selected = array_merge($this->_selected, match($config['fnqm']){
	 		 	 	 'N-QUALIFY' => $fieldrefs[$t_index],
	 		 	 	 'F-QUALIFY' => $this->get_fq_selected($tables[$t_index], $aliases[$t_index], $databases[$t_index], $fieldrefs[$t_index]),
	 		 	 	 'H-QUALIFY' => $this->get_hq_selected($tables[$t_index], $aliases[$t_index], $fieldrefs[$t_index])
	 		 	 });
	 		 }
	 	}else{
	 		$tmp_selected = $this->_selected;
	 		$this->_selected = [];
	 		foreach($tmp_selected as $cn){
	 			 #is this a real table field?
	 			 $allfields = array_merge(...$fieldrefs);
	 			 if(in_array($cn, $allfields)) {
                     echo "$elementToSearch found!";
                 }

	 			 //is the column name a fully qualified name?
	 			 $cn_parts = explode(".", $cn);
	 			 if(count($cn_parts) === 3){
	 			 	 $this->_selected[] = $cn;
	 			 	 continue;
	 			 }

	 			 //is the column name half qualified name.
	 			 if(count($cn_parts) === 2){
	 			 	 $table_name = $cn_parts[0];
	 			 	 $table_index = array_search($table_name, $tables);
	 			 	 if($table_index !== false){
	 			 	 	 $this->_selected[] = $databases[$table_index].".".$cn;
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
	 		     	 
	 		     	 //$this->_selected[] = $aliases[$cg_index] ? $databases[$cg_index].".".$aliases[$cg_index].".".$cn : $databases[$cg_index].".".$tables[$cg_index].".".$cn;

	 		     	 $this->_selected[] = $aliases[$cg_index] ? $aliases[$cg_index].".".$cn : $databases[$cg_index].".".$tables[$cg_index].".".$cn;
	 		     }
	 		}

	 	}
	 	return $this->_selected;
	 }*/

	 public function get_selected(...$config){
	 	 $tables    = $this->_context_tracker->get_tables();
 	     $aliases   = $this->_context_tracker->get_aliases();
 	     $databases = $this->_context_tracker->get_databases();
 	     $fieldrefs = $this->_context_tracker->get_fieldrefs();
 	     $ffsettings = $this->_context_tracker->get_ffsettings();

	 	 if(!$this->_selected){
	 		 $this->_selected = [];
	 		 foreach($fieldrefs as $t_index => $fields){
	 		 	 $real_fields = array_values($fields);
	 		 	 $this->_selected = array_merge($this->_selected, match($config['fnqm']){
	 		 	 	 'N-QUALIFY' => $real_fields,
	 		 	 	 'F-QUALIFY' => $this->get_fq_selected($tables[$t_index], $aliases[$t_index], $databases[$t_index], $real_fields),
	 		 	 	 'H-QUALIFY' => $this->get_hq_selected($tables[$t_index], $aliases[$t_index], $real_fields)
	 		 	 });
	 		 }
	 	 }else{ 
	 	     /**
	 	      * Assumptions:
	 	      * 1. if select fields are specified, then they will not be automatically qualified. To avoid naming conflicts the client must provide
	 	      *    fully qualified field names in the select list whenever necessary.
	 	      * 2. Select fields provided may come from more than one table, especially when joining one or more tables
	 	      * 
	 	      * Important
	 	      * 
	 	      * When joining tables that have file fields, the fields whose values will be used to find path, rename and default path for such
	 	      * file fields are added to the select list if:
	 	      * 1. The file fields are explicitly listed in the select fields list
	 	      * 2. The path, rename and dpath source fields have not be explcitly listed in the select fields list.
	 	      * */

	 	     foreach($ffsettings as $t_index => $settings){
	 	     	 $table_name = $tables[$t_index];
	 	     	 $db_name    = $databases[$t_index];
	 		 	 foreach($settings as $file_field => $file_field_config){
	 		 	 	 //the file field must be existing in the select fields list in any format: fully qualified or not
	 		 	 	 if( in_array($file_field, $this->_selected) || 
	 		 	 	 	 in_array($table_name.".".$file_field, $this->_selected) || in_array($db_name.".".$table_name.".".$file_field, $this->_selected)
	 		 	 	 ){
	 		 	 	 	 $file_meta_fields = array_unique(array_merge($file_field_config['path'], $file_field_config['rename'], $file_field_config['dpath']));
	 		 	 	 	 foreach($file_meta_fields as $fmf){
	 		 	 	 	 	 //the file meta field must not already be existing in the field list in any format: fully qualified or not
	 		 	 	 	 	 if(!in_array($fmf, $this->_selected) &&
			 		 	 	 	!in_array($table_name.".".$fmf, $this->_selected) &&
			 		 	 	 	!in_array($db_name.".".$table_name.".".$fmf, $this->_selected)
			 		 	 	 ){
			 		 	 	 	 $this->_selected[] = match($config['fnqm']){
					 		 	 	 'N-QUALIFY' => $fmf,
					 		 	 	 'F-QUALIFY' => $db_name.".".$table_name.".".$fmf,
					 		 	 	 'H-QUALIFY' => $table_name.".".$fmf
					 		 	 };
			 		 	 	 }
	 		 	 	 	 }
	 		 	 	 	 $this->_selected = array_unique($this->_selected);
	 		 	 	 }
	 		 	 }
	 		 }
	 	 }
	 	 return $this->_selected;
	 }
}
?>