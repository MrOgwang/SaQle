<?php
namespace SaQle\Dao\Filter\Parser;

use SaQle\Dao\Filter\Parser\Interfaces\IParser;

class Parser implements IParser{
	 public function parse_filters($group, $config){
		 $parsed = array("clause" => "", "values" => []);
		 if($group && count($group->filters()) > 0){
			 $parsed["clause"] .= "(";
			 foreach($group->filters() as $g){
				 if(is_object($g)){
					 if($g->is_group()){
						 $pf = $this->parse_filters($g, $config);
					     $parsed["clause"] .= $pf["clause"];
					     $parsed["values"] = array_merge($parsed["values"], $pf["values"]);
					 }else{
						 $filter_properties = $this->parse_basic_filter($g->filter(), $g->database(), $g->table(), $g->literal(), $config);
						 $parsed["clause"] .= $filter_properties->field ." ".$filter_properties->operator." ".$filter_properties->placeholder;
					     if(is_array($filter_properties->value)){
							 $parsed["values"] = array_merge($parsed["values"], $filter_properties->value);
						 }else{
							 array_push($parsed["values"], $filter_properties->value);
						 }
					 }
				 }else{
					 $g = $g == "&" ? "AND" : "OR";
					 $parsed["clause"] .= " ".strtoupper($g)." ";
				 }
			 }
			 $parsed["clause"] .= ")";
		 }
	     return $parsed;
	 }

     private function qualify_name($cn, $table, $database, $config){
     	 if($config['fnqm'] === 'N-QUALIFY')
     	 	return $cn;
     	 
     	 //is the column name a fully qualified name?
		 $cn_parts = explode(".", $cn);
		 if(count($cn_parts) === 3)
		 	 return $cn;

		 //is the column name half qualified name.
		 if(count($cn_parts) === 2)
		 	 //return $database.".".$cn;
		 	 return $cn;

         return $database.".".$table.".".$cn;
     }

	 private function parse_basic_filter($filter, $database, $table, $literal, $config){
		 $filter_object = ["placeholder" => "?"];
		 $first_index = strpos($filter, "~");
		 $secend_index = strpos($filter, "~", $first_index + 1);
		 $original_field = substr($filter, 0, $first_index);
		 $original_value = substr($filter, $secend_index + 1);
		 $filter_object["field"] = !$literal ? $this->qualify_name($original_field, $table, $database, $config) : $original_field;
		 $filter_object["operator"] = substr($filter, $first_index + 1, ($secend_index - $first_index - 1));

		 if(!$literal){
		 	 if($filter_object["operator"] == "IN"){
				 $original_value = str_replace(["(", ")"], "", $original_value);
				 $original_value_array = explode(", ", $original_value);
				 $filter_object ["placeholder"] = "(".str_repeat("?, ", count($original_value_array) - 1)."?)";
				 $filter_object["value"] = $original_value_array;
			 }else{
				 $filter_object["value"] = $original_value;
			 }
		 }else{
		 	 $filter_object ["placeholder"] = $original_value; //$this->qualify_name($original_value, $table, $database);
			 $filter_object["value"] = [];
		 }
		 return (object)$filter_object;
	 }
	 
}
?>