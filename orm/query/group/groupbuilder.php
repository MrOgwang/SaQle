<?php
namespace SaQle\Orm\Query\Group;

use SaQle\Orm\Database\Trackers\DbContextTracker;

class GroupBuilder{

	 //array of fields to group by
	 public ?array $fields = null {
	 	 set(?array $value){
	 	 	 $this->fields = $value;
	 	 }

	 	 get => $this->fields;
	 }

	 public function get_groupby(DbContextTracker $ctx, ...$config){
	 	if(!$this->fields)
	 		return [];

	 	if($config['fnqm'] === 'N-QUALIFY')
     	 	return $this->fields;

	 	$tables    = $ctx->tables;
	 	$aliases   = $ctx->aliases;
	 	$databases = $ctx->databases;
	 	$fieldrefs = $ctx->fieldrefs;
	 	
	 	$tmp_fields = $this->fields;
 		$this->fields = [];
 		foreach($tmp_fields as $cn){
 			 //is the column name a fully qualified name?
 			 $cn_parts = explode(".", $cn);
 			 if(count($cn_parts) === 3){
 			 	 $this->fields[] = $cn;
 			 	 continue;
 			 }

 			 //is the column name half qualified name.
 			 if(count($cn_parts) === 2){
 			 	 $table_name = $cn_parts[0];
 			 	 $table_index = array_search($table_name, $tables);
 			 	 if($table_index !== false){
 			 	 	 $this->fields[] = $databases[$table_index].".".$cn;
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
 		     	 $this->fields[] = $aliases[$cg_index] ? $databases[$cg_index].".".$aliases[$cg_index].".".$cn : $databases[$cg_index].".".$tables[$cg_index].".".$cn;
 		     }
 		}

	 	return $this->fields;
	 }
}
?>