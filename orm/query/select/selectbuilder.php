<?php
namespace SaQle\Orm\Query\Select;

use SaQle\Orm\Database\Trackers\DbContextTracker;

class SelectBuilder{
	 /**
	  * Includes is a list of navigational fields to get alongside the main select fields. 
	  * 
	  * Include fields will usually be added using the with method of the model manager unlike regular
	  * fields that will be added using the select method of the model manager
	  * */
	 public protected(set) ?array $includes = [] {
	 	 set(?array $value){
 	 	 	 $this->includes = $value;
 	 	 }

 	 	 get => $this->includes;
	 }

	 /**
	  * Selected is a list of the regular fields to include in the select sql statement
	  * 
	  * Regular fields will be added via the select method of the model manager
	  * */
	 public ?array $selected = [] {
	 	 set(?array $value){
 	 	 	 $this->selected = $value;
 	 	 }

 	 	 get => $this->selected;
	 }

	 /**
 	 * When selecting included fields, this keeps a record of the callbacks provided to fine tune the results
 	 * of an include field. This is a key => value array where key is the field name and value 
 	 * is the callback
 	 * */
 	 public array $withcallbacks = [] {
 	 	 set(array $value){
 	 	 	 $this->withcallbacks = $value;
 	 	 }

 	 	 get => $this->withcallbacks;
 	 }

	 public function add_include($field){
	 	 $includes       = !$this->includes ? [] : $this->includes;
	 	 $includes[]     = $field;
	 	 $this->includes = $includes;
	 }

	 private function get_fq_selected($table, $aliase, $database, $fields){
	 	 return array_map(function($f) use ($table, $aliase, $database){
		     #if there is a table aliase, don't include the database name in the fully qualified name.
		     return $aliase ? $aliase.".".$f : $database.".".$table.".".$f;
		 }, $fields);
	 }

	 private function get_hq_selected($table, $aliase, $fields){
	 	 return array_map(function($f) use ($table, $aliase){
		     return $aliase ? $aliase.".".$f : $table.".".$f;
		 }, $fields);
	 }

	 public function get_selected(DbContextTracker $ctx, ...$config){
	 	 $tables     = $ctx->tables;
 	     $aliases    = $ctx->aliases;
 	     $databases  = $ctx->databases;
 	     $fieldrefs  = $ctx->fieldrefs;
 	     $ffsettings = $ctx->ffsettings;

	 	 if(!$this->selected){
	 		 $this->selected = [];
	 		 $original_columns = [];
	 		
	 		 foreach($fieldrefs as $t_index => $fields){
	 		 	 $real_fields = array_values($fields);
	 		 	 $qualified_fields = match($config['fnqm']){
	 		 	 	 'N-QUALIFY' => $real_fields,
	 		 	 	 'F-QUALIFY' => $this->get_fq_selected($tables[$t_index], $aliases[$t_index], $databases[$t_index], $real_fields),
	 		 	 	 'H-QUALIFY' => $this->get_hq_selected($tables[$t_index], $aliases[$t_index], $real_fields)
	 		 	 };

	 		 	 foreach($real_fields as $rf_index => $rf){
	 		 	 	 if(in_array($rf, $original_columns)){ //there is a duplicate field that needs to be aliased
	 		 	 	 	 $table_ref = isset($aliases[$t_index]) && $aliases[$t_index] ? strtolower($aliases[$t_index]) : strtolower($tables[$t_index]);
	 		 	 	 	 $qualified_fields[$rf_index] = $qualified_fields[$rf_index]." AS ".$table_ref."_".$rf;
	 		 	 	 }
	 		 	 }
	 		 	 $original_columns = array_merge($original_columns, $real_fields);
	 		 	 $this->selected = array_merge($this->selected, $qualified_fields);
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

             $selected = $this->selected;
	 	     foreach($ffsettings as $t_index => $settings){
	 	     	 $table_name = $tables[$t_index];
	 	     	 $db_name    = $databases[$t_index];
	 		 	 foreach($settings as $file_field => $file_field_config){
	 		 	 	 //the file field must be existing in the select fields list in any format: fully qualified or not
	 		 	 	 if( in_array($file_field, $selected) || 
	 		 	 	 	 in_array($table_name.".".$file_field, $selected) || in_array($db_name.".".$table_name.".".$file_field, $selected)
	 		 	 	 ){
	 		 	 	 	 //$file_meta_fields = array_unique(array_merge($file_field_config['path'], $file_field_config['rename'], $file_field_config['dpath']));
	 		 	 	 	 $file_meta_fields = $file_field_config;
	 		 	 	 	 foreach($file_meta_fields as $fmf){
	 		 	 	 	 	 //the file meta field must not already be existing in the field list in any format: fully qualified or not
	 		 	 	 	 	 if(!in_array($fmf, $selected) &&
			 		 	 	 	!in_array($table_name.".".$fmf, $selected) &&
			 		 	 	 	!in_array($db_name.".".$table_name.".".$fmf, $selected)
			 		 	 	 ){
			 		 	 	 	 $selected[] = match($config['fnqm']){
					 		 	 	 'N-QUALIFY' => $fmf,
					 		 	 	 'F-QUALIFY' => $db_name.".".$table_name.".".$fmf,
					 		 	 	 'H-QUALIFY' => $table_name.".".$fmf
					 		 	 };
			 		 	 	 }
	 		 	 	 	 }
	 		 	 	 	 $selected = array_unique($selected);
	 		 	 	 }
	 		 	 }
	 		 }
	 		 $this->selected = $selected;
	 	 }
	 	 return $this->selected;
	 }
}
